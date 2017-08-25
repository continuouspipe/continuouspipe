package main

import (
    "k8s.io/client-go/kubernetes"
    "k8s.io/client-go/rest"
    "os"
    "errors"
    "time"
    "github.com/continuouspipe/continuouspipe/kube-resources-watcher/watcher"
    "strconv"
)

func main() {
    client, err := GetKubernetesClient()
    if err != nil {
        panic(err)
    }

    store, err := GetResourcesStore()
    if err != nil {
        panic(err)
    }

    resourceUpdater, err := GetResourceUpdater(client, store)
    if err != nil {
        panic(err)
    }

    w := watcher.Watcher{
        KubernetesClient: client,
        ResourceUpdater: resourceUpdater,
    }

    stop := make(chan struct{})
    go w.Watch(stop)
    for{
        time.Sleep(time.Second)
    }
}

func GetResourceUpdater(kubernetesClient *kubernetes.Clientset, store watcher.NamespaceResourceStore) (watcher.ResourceUpdater, error) {
    debounceSecondsString := os.Getenv("DEBOUNCE_SECONDS")
    if "" == debounceSecondsString {
        debounceSecondsString = "1"
    }

    debounceSeconds, err := strconv.ParseInt(debounceSecondsString, 10, 32)
    if err != nil {
        return nil, err
    }

    return watcher.NewDebouncedResourceUpdater(
        &watcher.DirectResourceUpdater{
            ResourceUsageCalculator: &watcher.KubernetesResourceUsageCalculator{
                KubernetesClient: kubernetesClient,
            },
            NamespaceResourceStore: store,
        },
        time.Duration(debounceSeconds) * time.Second,
    ), nil
}

func GetResourcesStore() (watcher.NamespaceResourceStore, error) {
    stores := []watcher.NamespaceResourceStore{
        &watcher.ScreenResourceStore{},
    }

    httpEndpoint := os.Getenv("HTTP_ENDPOINT")
    if "" != httpEndpoint {
        stores = append(stores, watcher.NewHttpResourceStore(httpEndpoint, os.Getenv("HTTP_ENDPOINT_BEARER_TOKEN")))
    }

    return watcher.NewCollectionNamespaceResourceStore(stores), nil
}

func GetKubernetesClient() (*kubernetes.Clientset, error) {
    clusterAddress := os.Getenv("CLUSTER_ADDRESS")
    clusterUsername := os.Getenv("CLUSTER_USERNAME")
    clusterPassword := os.Getenv("CLUSTER_PASSWORD")

    if clusterAddress == "" || clusterUsername == "" || clusterPassword == "" {
        return nil, errors.New("Cluster credentials are required")
    }

    return kubernetes.NewForConfig(&rest.Config{
        Host: clusterAddress,
        Username: clusterUsername,
        Password: clusterPassword,
        TLSClientConfig: rest.TLSClientConfig{
            Insecure: true,
        },
    })
}
