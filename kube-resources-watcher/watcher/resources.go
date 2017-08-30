package watcher

import (
    "k8s.io/client-go/kubernetes"
    "k8s.io/client-go/pkg/api/v1"
    meta_v1 "k8s.io/apimachinery/pkg/apis/meta/v1"
)

type NamespaceFinder interface {
    GetPodsInNamespace(namespace string) ([]v1.Pod, error)
    GetNamespace(namespace string) (v1.Namespace, error)
}

type KubernetesNamespaceFinder struct {
    kubernetesClient *kubernetes.Clientset
}

func (kpnf *KubernetesNamespaceFinder) GetPodsInNamespace(namespace string) ([]v1.Pod, error) {
    list, err := kpnf.kubernetesClient.Pods(namespace).List(meta_v1.ListOptions{})
    if err != nil {
        return []v1.Pod{}, err
    }

    return list.Items, nil
}

func (kpnf *KubernetesNamespaceFinder) GetNamespace(namespace string) (*v1.Namespace, error) {
    return kpnf.kubernetesClient.Namespaces().Get(namespace, meta_v1.GetOptions{})
}

func NewKubernetesNamespaceFinder(client *kubernetes.Clientset) *KubernetesNamespaceFinder {
    return &KubernetesNamespaceFinder{
        kubernetesClient: client,
    }
}

type NamespaceResourceUsageNamespace struct {
    Name string              `json:"name"`
    Labels map[string]string `json:"labels"`
}

type NamespaceResourceUsage struct {
    Namespace NamespaceResourceUsageNamespace `json:"namespace"`
    Limits    v1.ResourceList                 `json:"limits"`
    Requests  v1.ResourceList                 `json:"requests"`
}

type ResourceUsageCalculator interface {
    CalculateForNamespace(namespace string) (NamespaceResourceUsage, error)
}

type KubernetesResourceUsageCalculator struct {
    finder NamespaceFinder
}

func NewKubernetesResourceUsageCalculator(finder NamespaceFinder) *KubernetesResourceUsageCalculator {
    return &KubernetesResourceUsageCalculator{
        finder: finder,
    }
}

func (ruc* KubernetesResourceUsageCalculator) CalculateForNamespace(namespace string) (NamespaceResourceUsage, error) {
    pods, err := ruc.finder.GetPodsInNamespace(namespace)
    if err != nil {
        return NamespaceResourceUsage{}, err
    }

    usageNamespace, err := ruc.getUsageNamespace(namespace)
    if err != nil {
        return NamespaceResourceUsage{}, err
    }

    usage := NamespaceResourceUsage{
        Namespace: usageNamespace,
        Limits: v1.ResourceList{},
        Requests: v1.ResourceList{},
    }

    for _, pod := range pods {
        for _, container := range pod.Spec.Containers {
            usage.Limits = AddQuantity(usage.Limits, container.Resources.Limits)
            usage.Requests = AddQuantity(usage.Requests, container.Resources.Requests)
        }
    }

    return usage, nil
}

func (ruc* KubernetesResourceUsageCalculator) getUsageNamespace(name string) (NamespaceResourceUsageNamespace, error) {
    namespace, err := ruc.finder.GetNamespace(name)
    if err != nil {
        return NamespaceResourceUsageNamespace{}, err
    }

    return NamespaceResourceUsageNamespace{
        Name: namespace.Name,
        Labels: namespace.Labels,
    }, nil
}

func AddQuantity(list ...v1.ResourceList) v1.ResourceList {
    resources := v1.ResourceList{}

    for _, resourcesToAdd := range list {
        for resourceName, quantity := range resourcesToAdd {
            copiedQuantity := *(quantity.Copy())
            if otherQuantity, ok := resources[resourceName]; ok {
                copiedQuantity.Add(otherQuantity)
            }

            resources[resourceName] = copiedQuantity
        }
    }

    return resources
}
