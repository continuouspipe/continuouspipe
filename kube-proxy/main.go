package main

// Kube-proxy sits in between cp-remote and kubernetes forwarding https and spyd protocols
// Requires the following env settings to be set
//
// Suggested settings
//
// KUBE_PROXY_LISTEN_HTTPS      		true for testing locally, false on cp
// KUBE_PROXY_LISTEN_ADDRESS			https://localhost:80
// KUBE_PROXY_INSECURE_SKIP_VERIFY		true, unless we have valid certificates on the kubernetes side
// KUBE_PROXY_AUTHENTICATOR_HOST		for testing authenticator-staging.continuouspipe.io, on live authenticator.continuouspipe.io
// KUBE_PROXY_MASTER_API_KEY			cp master api key
//

import (
	"net/http"
	"net/url"
	"os"
	kproxy "github.com/continuouspipe/kube-proxy/proxy"
	"github.com/continuouspipe/kube-proxy/cplogs"
)

func main() {
	//Set the option as environment variables
	envListenHttps, _ := os.LookupEnv("KUBE_PROXY_LISTEN_HTTPS")
	envListenAddress, _ := os.LookupEnv("KUBE_PROXY_LISTEN_ADDRESS") //e.g.: https://localhost:80

	listenURL, err := url.Parse(envListenAddress)
	if err != nil {
		cplogs.V(5).Infof("Cannot parse URL: %v", err.Error())
	}
	h := kproxy.NewHttpHandler()

	if envListenHttps == "true" {
		err = http.ListenAndServeTLS(listenURL.Host, "server.crt", "server.key", h)
	} else {
		err = http.ListenAndServe(listenURL.Host, h)
	}

	if err != nil {
		cplogs.V(5).Infof("Error when listening: %v", err.Error())
	}
	cplogs.Flush()
}
