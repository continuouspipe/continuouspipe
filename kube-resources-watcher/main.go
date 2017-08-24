package main

import (
    "k8s.io/client-go/kubernetes"
    "k8s.io/client-go/rest"
    "os"
    "errors"
    "time"
    "github.com/continuouspipe/continuouspipe/kube-resources-watcher/watcher"
)

func main() {
    clusterAddress := os.Getenv("CLUSTER_ADDRESS")
    clusterUsername := os.Getenv("CLUSTER_USERNAME")
    clusterPassword := os.Getenv("CLUSTER_PASSWORD")

    if clusterAddress == "" || clusterUsername == "" || clusterPassword == "" {
        panic(errors.New("Cluster credentials are required"))

        return;
    }

    clientset, err := kubernetes.NewForConfig(&rest.Config{
        Host: clusterAddress,
        Username: clusterUsername,
        Password: clusterPassword,
        TLSClientConfig: rest.TLSClientConfig{
            Insecure: true,
        },
    })

    if err != nil {
        panic(err.Error())
    }

    w := watcher.Watcher{
        KubernetesClient: clientset,
        ResourceUsageCalculator: &watcher.KubernetesResourceUsageCalculator{
            KubernetesClient: clientset,
        },
        NamespaceResourceStore: &watcher.ScreenResourceStore{},
    }

    stop := make(chan struct{})
    go w.Watch(stop)
    for{
        time.Sleep(time.Second)
    }
}
