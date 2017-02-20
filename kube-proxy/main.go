package main

// Kube-proxy sits in between cp-remote and kubernetes forwarding https and spyd protocols
// Requires the following env settings to be set
//
// Suggested settings
//
// WILDCARD_SSL_CERT
// WILDCARD_SSL_KEY
// KUBE_PROXY_LISTEN_ADDRESS			https://localhost:443
// KUBE_PROXY_INSECURE_SKIP_VERIFY		true, unless we have valid certificates on the kubernetes side
// KUBE_PROXY_AUTHENTICATOR_HOST		for testing authenticator-staging.continuouspipe.io, on live authenticator.continuouspipe.io
// KUBE_PROXY_MASTER_API_KEY			cp master api key
//

import (
	"github.com/continuouspipe/kube-proxy/cplogs"
	kproxy "github.com/continuouspipe/kube-proxy/proxy"
	"net/http"
	"net/url"
	"os"
	"io/ioutil"
)

var envListenAddress, _ = os.LookupEnv("KUBE_PROXY_LISTEN_ADDRESS") //e.g.: https://localhost:80
var envWildcardSSLCert, _ = os.LookupEnv("WILDCARD_SSL_CERT")
var envWildcardSSLKey, _ = os.LookupEnv("WILDCARD_SSL_KEY")

var sslCertFileName = "server.crt"
var sslKeyFileName = "server.key"

func main() {
	if envWildcardSSLCert != "" && envWildcardSSLKey != "" {
		writeSSLCertAndKey()
	}

	listenURL, err := url.Parse(envListenAddress)
	if err != nil {
		cplogs.V(5).Infof("Cannot parse URL: %v", err.Error())
	}
	h := kproxy.NewHttpHandler()

	err = http.ListenAndServeTLS(listenURL.Host, sslCertFileName, sslKeyFileName, h)

	if err != nil {
		cplogs.V(5).Infof("Error when listening: %v", err.Error())
	}
	cplogs.Flush()
}

func writeSSLCertAndKey() {
	cplogs.V(5).Infoln("Writing provided SSL Cert")
	ioutil.WriteFile(sslCertFileName, []byte(envWildcardSSLCert), 0644)
	ioutil.WriteFile(sslKeyFileName, []byte(envWildcardSSLKey), 0644)
}
