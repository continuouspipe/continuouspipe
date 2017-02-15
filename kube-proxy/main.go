package main

import (
	"flag"
	"fmt"
	"net/http"
	"net/url"
	"os"
	kproxy "github.com/continuouspipe/kube-proxy/proxy"
	"github.com/spf13/viper"
	"github.com/continuouspipe/kube-proxy/cplogs"
)

const listenUrl = "https://127.0.0.1:8080"

var insecure = flag.Bool("insecure", false, "insecure")

func main() {
	flag.Parse()
	listenURL, err := url.Parse(listenUrl)
	if err != nil {
		cplogs.V(5).Infof("Cannot parse URL: %v", err.Error())
	}
	initConfigFile()
	h := kproxy.NewHttpHandler()
	h.InsecureSkipVerify = *insecure

	err = http.ListenAndServeTLS(listenURL.Host, "server.crt", "server.key", h)
	if err != nil {
		cplogs.V(5).Infof("Error when listening: %v", err.Error())
	}
	cplogs.Flush()
}

func initConfigFile() {
	viper.SetConfigFile(".kubeproxy.yml")
	viper.SetConfigType("yml")
	pwd, err := os.Getwd()
	if err != nil {
		fmt.Println(err)
		cplogs.Flush()
		os.Exit(1)
	}
	viper.AddConfigPath(pwd)
	err = viper.ReadInConfig()
	if err != nil {
		cplogs.V(5).Infof("Cannot load config file: %v", err.Error())
	}
	cplogs.Flush()
}
