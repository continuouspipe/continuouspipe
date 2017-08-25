package watcher

import (
    "fmt"
    "net/http"
    "time"
    "k8s.io/kubernetes/pkg/util/json"
    "bytes"
)

type NamespaceResourceStore interface {
    Store(usage NamespaceResourceUsage) error
}

func NewCollectionNamespaceResourceStore(collection []NamespaceResourceStore) *CollectionNamespaceResourceStore {
    return &CollectionNamespaceResourceStore{
        collection,
    }
}
type CollectionNamespaceResourceStore struct {
    collection []NamespaceResourceStore
}

func (me *CollectionNamespaceResourceStore) Store(usage NamespaceResourceUsage) error {
    for _, store := range me.collection {
        err := store.Store(usage)

        if err != nil {
            return err
        }
    }

    return nil
}

type ScreenResourceStore struct {}
func (me *ScreenResourceStore) Store(usage NamespaceResourceUsage) error {
    fmt.Printf("Namespace \"%s\": %s CPU requests, %s memory requests\n", usage.Namespace.Name, usage.Requests.Cpu().String(), usage.Requests.Memory().String())

    return nil
}

func NewHttpResourceStore(endpointUrl string, bearerToken string) *HttpResourceStore {
    return &HttpResourceStore{
        endpoint: endpointUrl,
        bearerToken: bearerToken,
        httpClient: &http.Client{
            Timeout: time.Second * 10,
        },
    }
}

type HttpResourceStore struct {
    endpoint string
    bearerToken string
    httpClient *http.Client
}

type HttpResourceReport struct {
    Namespace string                 `json:"namespace"`
    Resources NamespaceResourceUsage `json:"resources"`
}

func (me *HttpResourceStore) Store(usage NamespaceResourceUsage) error {
    body, err := json.Marshal(usage)
    if err != nil {
        return err
    }

    req, err := http.NewRequest("POST", me.endpoint, bytes.NewReader(body))
    if err != nil {
        return err
    }

    req.Header.Set("Content-Type", "application/json")

    if me.bearerToken != "" {
        req.Header.Set("Authorization", "Bearer "+me.bearerToken)
    }

    _, err = me.httpClient.Do(req)

    return err
}
