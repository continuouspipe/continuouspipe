package watcher

import "fmt"

type NamespaceResourceStore interface {
    StoreUsage(namespace string, resources NamespaceResourceUsage) error
}

type ScreenResourceStore struct {}
func (me *ScreenResourceStore) StoreUsage(namespace string, resources NamespaceResourceUsage) error {
    fmt.Printf("Namespace \"%s\": %s CPU requests, %s memory requests\n", namespace, resources.Requests.Cpu().String(), resources.Requests.Memory().String())

    return nil
}
