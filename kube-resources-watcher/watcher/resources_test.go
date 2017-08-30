package watcher

import (
    "testing"
    "k8s.io/client-go/pkg/api/v1"
    metav1 "k8s.io/apimachinery/pkg/apis/meta/v1"
    "k8s.io/apimachinery/pkg/api/resource"
)

type InMemoryFinder struct {
    Namespace v1.Namespace
    Pods      []v1.Pod
}

func (imf *InMemoryFinder) GetPodsInNamespace(namespace string) ([]v1.Pod, error) {
    return imf.Pods, nil
}

func (imf *InMemoryFinder) GetNamespace(namespace string) (v1.Namespace, error) {
    return imf.Namespace, nil
}

func NewInMemoryFinder(Namespace v1.Namespace, Pods []v1.Pod) *InMemoryFinder {
    return &InMemoryFinder{
        Namespace: Namespace,
        Pods: Pods,
    }
}

func TestItAddsAllContainerResources(t *testing.T) {
    calculator := NewKubernetesResourceUsageCalculator(NewInMemoryFinder(
        v1.Namespace{
            ObjectMeta: metav1.ObjectMeta{
                Name: "foo",
            },
        },
        []v1.Pod{
            {
                Spec: v1.PodSpec{
                    Containers: []v1.Container{
                        {
                            Resources: v1.ResourceRequirements{
                                Limits: v1.ResourceList{
                                    v1.ResourceCPU: resource.MustParse("10m"),
                                    v1.ResourceMemory: resource.MustParse("200Mi"),
                                },
                            },
                        },
                    },
                },
            },
            {
                Spec: v1.PodSpec{
                    Containers: []v1.Container{
                        {
                            Resources: v1.ResourceRequirements{
                                Limits: v1.ResourceList{
                                    v1.ResourceCPU: resource.MustParse("2"),
                                    v1.ResourceMemory: resource.MustParse("2Gi"),
                                },
                            },
                        },
                    },
                },
            },
        },
    ))

    usage, err := calculator.CalculateForNamespace("foo")
    if err != nil {
        t.Error(err)
    }

    if usage.Limits.Cpu().Cmp(resource.MustParse("2010m")) != 0 {
        t.Errorf("Found the following CPU instead: %s", usage.Limits.Cpu())
    }

    if usage.Limits.Memory().Cmp(resource.MustParse("2248Mi")) != 0 {
        t.Errorf("Found the following memory instead: %s", usage.Limits.Memory())
    }
}
