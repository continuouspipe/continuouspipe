package proxy

import (
	"bufio"
	"crypto/tls"
	"fmt"
	"github.com/continuouspipe/kube-proxy/cpapi"
	"github.com/continuouspipe/kube-proxy/keenapi"
	"github.com/continuouspipe/kube-proxy/parser"
	"github.com/golang/glog"
	"io"
	"io/ioutil"
	"net"
	"net/http"
	"net/http/httputil"
	"net/url"
	"os"
	"strings"
	"time"
)

const ISO8601 = "2006-01-02T15:04:05.999-0700"

var envInsecureSkipVerify, _ = os.LookupEnv("KUBE_PROXY_INSECURE_SKIP_VERIFY")

type HttpHandler struct {
	keenapi *keenapi.Sender
}

func NewHttpHandler() *HttpHandler {
	h := &HttpHandler{}
	h.keenapi = keenapi.NewSender()
	return h
}

func (m *HttpHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	glog.V(5).Infof("Start serving request %s", r.URL.String())

	proxy, err := m.NewUpgradeAwareSingleHostReverseProxy(r)
	if err != nil {
		glog.Errorf("Error when creating the single host reverse proxy. " + err.Error())
		glog.Flush()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	glog.Flush()
	proxy.ServeHTTP(w, r)
}

// UpgradeAwareSingleHostReverseProxy is capable of proxying both regular HTTP
// connections and those that require upgrading (e.g. web sockets). It implements
// the http.RoundTripper and http.Handler interfaces.
type UpgradeAwareSingleHostReverseProxy struct {
	transport          http.RoundTripper
	reverseProxy       *httputil.ReverseProxy
	apiCluster         *cpapi.ApiCluster
	cpToKube           parser.CpToKubeUrlParser
	insecureSkipVerify bool
}

// NewUpgradeAwareSingleHostReverseProxy creates a new UpgradeAwareSingleHostReverseProxy.
func (m HttpHandler) NewUpgradeAwareSingleHostReverseProxy(r *http.Request) (*UpgradeAwareSingleHostReverseProxy, error) {
	start := time.Now()
	transport := http.DefaultTransport.(*http.Transport)
	if envInsecureSkipVerify == "true" {
		glog.V(5).Infoln("InsecureSkipVerify enabled")
		transport.TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	}

	user, password, ok := r.BasicAuth()
	if ok != true {
		access_token := r.URL.Query().Get("access_token")

		if access_token != "" {
			user = "x-token-auth"
			password = access_token
		} else {
			return nil, fmt.Errorf("Auth has failed (basic & query)")
		}
	}

	cinfo := cpapi.NewClusterInfo()
	cpToKube := parser.NewCpToKubeUrl()

	flowId, err := cpToKube.ExtractFlowId(r.URL.Path)
	if err != nil {
		return nil, err
	}
	clusterId, err := cpToKube.ExtractClusterId(r.URL.Path)
	if err != nil {
		return nil, err
	}

	apiCluster, err := cinfo.GetCluster(user, password, flowId, clusterId)
	if err != nil {
		return nil, fmt.Errorf("Failed to get the cluster url, " + err.Error())
	}

	backendAddr, err := url.Parse(apiCluster.Address)
	if err != nil {
		return nil, fmt.Errorf("Failed to parse the cluster url, " + err.Error())
	}

	reverseProxy := httputil.NewSingleHostReverseProxy(backendAddr)
	reverseProxy.FlushInterval = 200 * time.Millisecond
	p := &UpgradeAwareSingleHostReverseProxy{
		apiCluster:         apiCluster,
		transport:          transport,
		reverseProxy:       reverseProxy,
		cpToKube:           cpToKube,
		insecureSkipVerify: envInsecureSkipVerify == "true",
	}
	p.reverseProxy.Transport = p

	end := time.Now()
	go func(s time.Time, e time.Time) {
		duration := e.Sub(s)
		m.keenapi.Send(&keenapi.KeenApiPayload{
			r.URL.String(),
			s.Format(ISO8601),
			e.Format(ISO8601),
			duration.Nanoseconds() / 1e6,
			"reverse proxy init"})
	}(start, end)
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
		glog.V(5).Infof("got unauthorized error from backend for: %s %s", req.Method, req.URL)
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

	backendURL, err := url.Parse(p.apiCluster.Address)
	if err != nil {
		return nil, fmt.Errorf("Failed to parse the cluster url")
	}

	// if backendAddr is http://host/base and req is for /foo, the resulting path
	// for backendURL should be /base/foo
	pathWithoutCpData, err := p.cpToKube.RemoveCpDataFromUri(singleJoiningSlash(backendURL.Path, req.URL.Path))
	if err != nil {
		return nil, err
	}
	backendURL.Path = pathWithoutCpData
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
		glog.V(5).Infof("Error creating backend request: %s", err)
		http.Error(w, err.Error(), http.StatusInternalServerError)
		glog.Flush()
		return
	}

	req.SetBasicAuth(p.apiCluster.Username, p.apiCluster.Password)

	if !p.isUpgradeRequest(req) {
		p.reverseProxy.ServeHTTP(w, newReq)
		glog.Flush()
		return
	}

	p.serveUpgrade(w, newReq)
	glog.Flush()
}

func (p *UpgradeAwareSingleHostReverseProxy) dialBackend(req *http.Request) (net.Conn, error) {
	dialAddr := canonicalAddr(req.URL)

	backendURL, err := url.Parse(p.apiCluster.Address)
	if err != nil {
		return nil, fmt.Errorf("Failed to parse the cluster url")
	}

	switch backendURL.Scheme {
	case "http":
		return net.Dial("tcp", dialAddr)
	case "https":
		tlsConfig := new(tls.Config)
		if p.insecureSkipVerify {
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
		return nil, fmt.Errorf("unknown scheme: %s", backendURL.Scheme)
	}
}

func (p *UpgradeAwareSingleHostReverseProxy) serveUpgrade(w http.ResponseWriter, req *http.Request) {
	backendConn, err := p.dialBackend(req)
	if err != nil {
		glog.V(5).Infof("Error connecting to backend: %s", err)
		w.WriteHeader(http.StatusServiceUnavailable)
		return
	}
	defer backendConn.Close()

	err = req.Write(backendConn)
	if err != nil {
		glog.V(5).Infof("Error writing request to backend: %s", err)
		return
	}

	resp, err := http.ReadResponse(bufio.NewReader(backendConn), req)
	if err != nil {
		glog.V(5).Infof("Error reading response from backend: %s", err)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte("Internal Server Error"))
		return
	}

	if resp.StatusCode == http.StatusUnauthorized {
		glog.V(5).Infof("Got unauthorized error from backend for: %s %s", req.Method, req.URL)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte("Internal Server Error"))
		return
	}

	requestHijackedConn, _, err := w.(http.Hijacker).Hijack()
	if err != nil {
		glog.V(5).Infof("Error hijacking request connection: %s", err)
		return
	}
	defer requestHijackedConn.Close()

	// NOTE: from this point forward, we own the connection and we can't use
	// w.Header(), w.Write(), or w.WriteHeader any more

	err = resp.Write(requestHijackedConn)
	if err != nil {
		glog.V(5).Infof("Error writing backend response to client: %s", err)
		return
	}

	done := make(chan struct{}, 2)

	go func() {
		_, err := io.Copy(backendConn, requestHijackedConn)
		if err != nil && !strings.Contains(err.Error(), "use of closed network connection") {
			glog.V(5).Infof("error proxying data from client to backend: %v", err)
		}
		done <- struct{}{}
	}()

	go func() {
		_, err := io.Copy(requestHijackedConn, backendConn)
		if err != nil && !strings.Contains(err.Error(), "use of closed network connection") {
			glog.V(5).Infof("error proxying data from backend to client: %v", err)
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
