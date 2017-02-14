package main

import (
	"flag"
	"fmt"
	"log"
	"net/http"
	"net/url"
	kproxy "github.com/continuouspipe/kube-proxy/proxy"
)

const listenUrl = "https://127.0.0.1:8080"

func main() {
	flag.Parse()

	listenURL, err := url.Parse(listenUrl)
	if err != nil {
		log.Fatalf("Cannot parse URL: %v", err)
	}

	if err != nil {
		fmt.Errorf("Unable to initialize the Kubernetes proxy: %v", err)
	}

	h := kproxy.NewHttpHandler()

	log.Fatal(http.ListenAndServeTLS(listenURL.Host, "server.crt", "server.key", h))
}
