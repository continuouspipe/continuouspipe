package watcher

import (
    "k8s.io/client-go/kubernetes"
    "fmt"
    "k8s.io/client-go/tools/cache"
    metav1 "k8s.io/apimachinery/pkg/apis/meta/v1"
    "k8s.io/apimachinery/pkg/fields"
    "k8s.io/client-go/pkg/api/v1"
    "time"
    "reflect"
)

type Watcher struct {
    KubernetesClient *kubernetes.Clientset
    ResourceUsageCalculator
    NamespaceResourceStore
}

func (w *Watcher) Watch(stopCh <-chan struct{}) {
    fmt.Println("Watching for events...")

    watchlist := cache.NewListWatchFromClient(w.KubernetesClient.Core().RESTClient(), "pods", metav1.NamespaceAll, fields.Everything())

    _, controller := cache.NewInformer(
        watchlist,
        &v1.Pod{},
        time.Second * 0,
        cache.ResourceEventHandlerFuncs{
            AddFunc: func(obj interface{}) {
                w.ObjectHasChanged(obj)
            },
            DeleteFunc: func(obj interface{}) {
                w.ObjectHasChanged(obj)
            },
            UpdateFunc:func(oldObj, newObj interface{}) {
                w.ObjectHasChanged(newObj)
            },
        },
    )

    controller.Run(stopCh)
}

func (w *Watcher) ObjectHasChanged(obj interface{}) {
    pod, ok := obj.(*v1.Pod)

    if !ok {
        fmt.Printf("ERROR: Cannot transform to pod: %s", reflect.TypeOf(obj))
        return;
    }

    err := w.UpdateNamespaceResources(pod.Namespace)
    if err != nil {
        fmt.Println("ERROR: "+err.Error())
    }
}

func (w *Watcher) UpdateNamespaceResources(namespace string) error {
    usage, err := w.ResourceUsageCalculator.CalculateForNamespace(namespace)
    if err != nil {
        return err
    }

    return w.NamespaceResourceStore.StoreUsage(namespace, usage)
}
