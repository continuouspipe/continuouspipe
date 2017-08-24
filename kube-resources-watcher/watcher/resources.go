package watcher

import (
    "k8s.io/client-go/kubernetes"
    "k8s.io/client-go/pkg/api/v1"
    meta_v1 "k8s.io/apimachinery/pkg/apis/meta/v1"
)

type NamespaceResourceUsage struct {
    Limits   v1.ResourceList
    Requests v1.ResourceList
}

type ResourceUsageCalculator interface {
    CalculateForNamespace(namespace string) (NamespaceResourceUsage, error)
}

type KubernetesResourceUsageCalculator struct {
    KubernetesClient *kubernetes.Clientset
}

func (ruc* KubernetesResourceUsageCalculator) CalculateForNamespace(namespace string) (NamespaceResourceUsage, error) {
    list, err := ruc.KubernetesClient.Pods(namespace).List(meta_v1.ListOptions{})
    if err != nil {
        return NamespaceResourceUsage{}, err
    }

    usage := NamespaceResourceUsage{
        Limits: v1.ResourceList{},
        Requests: v1.ResourceList{},
    }

    for _, pod := range list.Items {
        for _, container := range pod.Spec.Containers {
            AddQuantity(&usage.Limits, container.Resources.Limits)
            AddQuantity(&usage.Requests, container.Resources.Requests)
        }
    }

    return usage, nil
}

func AddQuantity(to *v1.ResourceList, from v1.ResourceList) {
    for resourceName, quantity := range from {
        if val, ok := (*to)[resourceName]; ok {
            val.Add(quantity)
        } else {
            (*to)[resourceName] = quantity
        }
    }
}

// Throttled calculator
