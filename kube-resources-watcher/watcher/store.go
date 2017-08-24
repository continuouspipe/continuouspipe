package watcher

import (
    "fmt"
    "net/http"
    "time"
    "k8s.io/kubernetes/pkg/util/json"
    "bytes"
)

type NamespaceResourceStore interface {
    StoreUsage(namespace string, resources NamespaceResourceUsage) error
}

func NewCollectionNamespaceResourceStore(collection []NamespaceResourceStore) *CollectionNamespaceResourceStore {
    return &CollectionNamespaceResourceStore{
        collection,
    }
}
type CollectionNamespaceResourceStore struct {
    collection []NamespaceResourceStore
}

func (me *CollectionNamespaceResourceStore) StoreUsage(namespace string, resources NamespaceResourceUsage) error {
    for _, store := range me.collection {
        err := store.StoreUsage(namespace, resources)

        if err != nil {
            return err
        }
    }

    return nil
}

type ScreenResourceStore struct {}
func (me *ScreenResourceStore) StoreUsage(namespace string, resources NamespaceResourceUsage) error {
    fmt.Printf("Namespace \"%s\": %s CPU requests, %s memory requests\n", namespace, resources.Requests.Cpu().String(), resources.Requests.Memory().String())

    return nil
}

func NewHttpResourceStore(endpointUrl string) *HttpResourceStore {
    return &HttpResourceStore{
        endpoint: endpointUrl,
        httpClient: &http.Client{
            Timeout: time.Second * 10,
        },
    }
}

type HttpResourceStore struct {
    endpoint string
    httpClient *http.Client
}

type HttpResourceReport struct {
    Namespace string                 `json:"namespace"`
    Resources NamespaceResourceUsage `json:"resources"`
}

func (me *HttpResourceStore) StoreUsage(namespace string, resources NamespaceResourceUsage) error {
    body, err := json.Marshal(HttpResourceReport{
        Namespace: namespace,
        Resources: resources,
    })

    if err != nil {
        return err
    }

    _, err = me.httpClient.Post(me.endpoint, "application/json", bytes.NewReader(body))

    return err
}
