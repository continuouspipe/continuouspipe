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
// KUBE_PROXY_RIVER_HOST				for testing river-staging.continuouspipe.io, on live authenticator.continuouspipe.io
// KUBE_PROXY_MASTER_API_KEY			cp master api key
// KEEN_IO_PROJECT_ID                   the keen.io project id
// KEEN_IO_EVENT_COLLECTION             master
// KEEN_IO_WRITE_KEY                    the keen.io write key
//

import (
	"encoding/base64"
	"flag"
	"fmt"
	kproxy "github.com/continuouspipe/kube-proxy/proxy"
	"github.com/golang/glog"
	"io/ioutil"
	"net/http"
	"net/url"
	"os"
)

var envListenAddress, _ = os.LookupEnv("KUBE_PROXY_LISTEN_ADDRESS") //e.g.: https://localhost:80
var envWildcardSSLCert, _ = os.LookupEnv("WILDCARD_SSL_CERT")
var envWildcardSSLKey, _ = os.LookupEnv("WILDCARD_SSL_KEY")

var sslCertFileName = "server.crt"
var sslKeyFileName = "server.key"

func main() {
	//parse the flags before glog start using them
	flag.Parse()

	if envWildcardSSLCert != "" && envWildcardSSLKey != "" {
		writeSSLCertAndKey()
	}

	listenURL, err := url.Parse(envListenAddress)
	if err != nil {
		glog.V(5).Infof("Cannot parse URL: %v\n", err.Error())
		glog.Flush()
		fmt.Printf("Cannot parse URL: %v\n", err.Error())
		os.Exit(1)
	}
	h := kproxy.NewHttpHandler()

	err = http.ListenAndServeTLS(listenURL.Host, sslCertFileName, sslKeyFileName, h)

	if err != nil {
		glog.V(5).Infof("Error when listening: %v\n", err.Error())
		glog.Flush()
		fmt.Printf("Error when listening: %v\n", err.Error())
		os.Exit(1)
	}
	glog.Flush()
}

func writeSSLCertAndKey() {
	glog.V(5).Infoln("Writing provided SSL Cert")

	wildcardSSLCert, err := base64.StdEncoding.DecodeString(envWildcardSSLCert)
	if err != nil {
		glog.V(5).Infof("Error decoding wildcardSSLCert: %v\n", err.Error())
		glog.Flush()
		fmt.Printf("Error decoding wildcardSSLCert: %v\n", err.Error())
		os.Exit(1)
	}
	wildcardSSLKey, err := base64.StdEncoding.DecodeString(envWildcardSSLKey)
	if err != nil {
		glog.V(5).Infof("Error decoding wildcardSSLKey: %v\n", err.Error())
		glog.Flush()
		fmt.Printf("Error decoding wildcardSSLKey: %v\n", err.Error())
		os.Exit(1)
	}

	ioutil.WriteFile(sslCertFileName, []byte(wildcardSSLCert), 0644)
	ioutil.WriteFile(sslKeyFileName, []byte(wildcardSSLKey), 0644)
}
