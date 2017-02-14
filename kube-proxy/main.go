package main

import (
	"bufio"
	"crypto/tls"
	"flag"
	"fmt"
	"io"
	"io/ioutil"
	"log"
	"net"
	"net/http"
	"net/http/httputil"
	"net/url"
	"strings"
	"time"
)

var insecure = flag.Bool("insecure", false, "insecure")

const listenUrl = "https://127.0.0.1:8080"
const kubeUrl = ""
const kubeUser = ""
const kubePassword = ""

func main() {
	flag.Parse()

	listenURL, err := url.Parse(listenUrl)
	if err != nil {
		log.Fatalf("Cannot parse URL: %v", err)
	}

	if err != nil {
		fmt.Errorf("Unable to initialize the Kubernetes proxy: %v", err)
	}
	log.Fatal(http.ListenAndServeTLS(listenURL.Host, "server.crt", "server.key", NewMultiHandler()))
}

type MultiHandler struct{}

func NewMultiHandler() *MultiHandler {
	return &MultiHandler{}
}

func (m *MultiHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	targetURL, err := url.Parse(kubeUrl)
	if err != nil {
		log.Fatalf("Cannot parse URL: %v", err)
	}
	proxy, err := NewUpgradeAwareSingleHostReverseProxy(targetURL)
	proxy.ServeHTTP(w, r)
}

// UpgradeAwareSingleHostReverseProxy is capable of proxying both regular HTTP
// connections and those that require upgrading (e.g. web sockets). It implements
// the http.RoundTripper and http.Handler interfaces.
type UpgradeAwareSingleHostReverseProxy struct {
	backendAddr  *url.URL
	transport    http.RoundTripper
	reverseProxy *httputil.ReverseProxy
}

// NewUpgradeAwareSingleHostReverseProxy creates a new UpgradeAwareSingleHostReverseProxy.
func NewUpgradeAwareSingleHostReverseProxy(backendAddr *url.URL) (*UpgradeAwareSingleHostReverseProxy, error) {
	transport := http.DefaultTransport.(*http.Transport)
	if *insecure {
		transport.TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	}

	reverseProxy := httputil.NewSingleHostReverseProxy(backendAddr)
	reverseProxy.FlushInterval = 200 * time.Millisecond
	p := &UpgradeAwareSingleHostReverseProxy{
		backendAddr:  backendAddr,
		transport:    transport,
		reverseProxy: reverseProxy,
	}
	p.reverseProxy.Transport = p
	return p, nil
}

// RoundTrip sends the request to the backend and strips off the CORS headers
// before returning the response.
func (p *UpgradeAwareSingleHostReverseProxy) RoundTrip(req *http.Request) (*http.Response, error) {
	resp, err := p.transport.RoundTrip(req)
	if err != nil {
		return resp, err
	}

	if resp.StatusCode == http.StatusUnauthorized {
		log.Printf("got unauthorized error from backend for: %s %s", req.Method, req.URL)
		// Internal error, backend didn't recognize proxy identity
		// Surface as a server error to the client
		resp = &http.Response{
			StatusCode:    http.StatusInternalServerError,
			Status:        http.StatusText(http.StatusInternalServerError),
			Body:          ioutil.NopCloser(strings.NewReader("Internal Server Error")),
			ContentLength: -1,
		}
	}

	return resp, err
}

func (p *UpgradeAwareSingleHostReverseProxy) newProxyRequest(req *http.Request) (*http.Request, error) {
	// the new request for the backend? Do we need to copy anything else?
	//
	backendURL := *p.backendAddr
	// if backendAddr is http://host/base and req is for /foo, the resulting path
	// for backendURL should be /base/foo
	backendURL.Path = singleJoiningSlash(backendURL.Path, req.URL.Path)
	backendURL.RawQuery = req.URL.RawQuery

	newReq, err := http.NewRequest(req.Method, backendURL.String(), req.Body)
	if err != nil {
		return nil, err
	}
	newReq.Header = req.Header

	return newReq, nil
}

func (p *UpgradeAwareSingleHostReverseProxy) isUpgradeRequest(req *http.Request) bool {
	for _, h := range req.Header[http.CanonicalHeaderKey("Connection")] {
		if strings.Contains(strings.ToLower(h), "upgrade") {
			return true
		}
	}
	return false
}

// ServeHTTP inspects the request and either proxies an upgraded connection directly,
// or uses httputil.ReverseProxy to proxy the normal request.
func (p *UpgradeAwareSingleHostReverseProxy) ServeHTTP(w http.ResponseWriter, req *http.Request) {
	newReq, err := p.newProxyRequest(req)
	if err != nil {
		log.Printf("Error creating backend request: %s", err)
		w.WriteHeader(http.StatusInternalServerError)
		return
	}

	//set basic auth
	req.SetBasicAuth(kubeUser, kubePassword)

	if !p.isUpgradeRequest(req) {
		p.reverseProxy.ServeHTTP(w, newReq)
		return
	}

	p.serveUpgrade(w, newReq)
}

func (p *UpgradeAwareSingleHostReverseProxy) dialBackend(req *http.Request) (net.Conn, error) {
	dialAddr := canonicalAddr(req.URL)

	switch p.backendAddr.Scheme {
	case "http":
		return net.Dial("tcp", dialAddr)
	case "https":
		tlsConfig := new(tls.Config)
		if *insecure {
			tlsConfig.InsecureSkipVerify = true
		}
		tlsConn, err := tls.Dial("tcp", dialAddr, tlsConfig)
		if err != nil {
			return nil, err
		}
		// TODO
		// hostToVerify, _, err := net.SplitHostPort(dialAddr)
		// if err != nil {
		//   return nil, err
		// }
		// err = tlsConn.VerifyHostname(hostToVerify)
		return tlsConn, err
	default:
		return nil, fmt.Errorf("unknown scheme: %s", p.backendAddr.Scheme)
	}
}

func (p *UpgradeAwareSingleHostReverseProxy) serveUpgrade(w http.ResponseWriter, req *http.Request) {
	backendConn, err := p.dialBackend(req)
	if err != nil {
		log.Printf("Error connecting to backend: %s", err)
		w.WriteHeader(http.StatusServiceUnavailable)
		return
	}
	defer backendConn.Close()

	err = req.Write(backendConn)
	if err != nil {
		log.Printf("Error writing request to backend: %s", err)
		return
	}

	resp, err := http.ReadResponse(bufio.NewReader(backendConn), req)
	if err != nil {
		log.Printf("Error reading response from backend: %s", err)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte("Internal Server Error"))
		return
	}

	if resp.StatusCode == http.StatusUnauthorized {
		log.Printf("Got unauthorized error from backend for: %s %s", req.Method, req.URL)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte("Internal Server Error"))
		return
	}

	requestHijackedConn, _, err := w.(http.Hijacker).Hijack()
	if err != nil {
		log.Printf("Error hijacking request connection: %s", err)
		return
	}
	defer requestHijackedConn.Close()

	// NOTE: from this point forward, we own the connection and we can't use
	// w.Header(), w.Write(), or w.WriteHeader any more

	err = resp.Write(requestHijackedConn)
	if err != nil {
		log.Printf("Error writing backend response to client: %s", err)
		return
	}

	done := make(chan struct{}, 2)

	go func() {
		_, err := io.Copy(backendConn, requestHijackedConn)
		if err != nil && !strings.Contains(err.Error(), "use of closed network connection") {
			log.Printf("error proxying data from client to backend: %v", err)
		}
		done <- struct{}{}
	}()

	go func() {
		_, err := io.Copy(requestHijackedConn, backendConn)
		if err != nil && !strings.Contains(err.Error(), "use of closed network connection") {
			log.Printf("error proxying data from backend to client: %v", err)
		}
		done <- struct{}{}
	}()

	<-done
}

// borrowed from net/http/httputil/reverseproxy.go
func singleJoiningSlash(a, b string) string {
	aslash := strings.HasSuffix(a, "/")
	bslash := strings.HasPrefix(b, "/")
	switch {
	case aslash && bslash:
		return a + b[1:]
	case !aslash && !bslash:
		return a + "/" + b
	}
	return a + b
}

// FROM: http://golang.org/src/net/http/client.go
// Given a string of the form "host", "host:port", or "[ipv6::address]:port",
// return true if the string includes a port.
func hasPort(s string) bool { return strings.LastIndex(s, ":") > strings.LastIndex(s, "]") }

// FROM: http://golang.org/src/net/http/transport.go
var portMap = map[string]string{
	"http":  "80",
	"https": "443",
}

// FROM: http://golang.org/src/net/http/transport.go
// canonicalAddr returns url.Host but always with a ":port" suffix
func canonicalAddr(url *url.URL) string {
	addr := url.Host
	if !hasPort(addr) {
		return addr + ":" + portMap[url.Scheme]
	}
	return addr
}
