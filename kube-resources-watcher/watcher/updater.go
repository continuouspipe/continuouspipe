package watcher

import (
    "time"
)

type ResourceUpdater interface {
    Update(namespace string) error
}

type DirectResourceUpdater struct {
    ResourceUsageCalculator
    NamespaceResourceStore
}

func (dru* DirectResourceUpdater) Update(namespace string) error {
    usage, err := dru.ResourceUsageCalculator.CalculateForNamespace(namespace)
    if err != nil {
        return err
    }

    return dru.NamespaceResourceStore.StoreUsage(namespace, usage)
}

func NewDebouncedResourceUpdater(decoratedUpdater ResourceUpdater, debouncingPeriod time.Duration) *DebouncedResourceUpdater {
    return &DebouncedResourceUpdater{
        decoratedUpdater: decoratedUpdater,
        namespaceChannels: map[string]chan string{},
        period: debouncingPeriod,
    }
}

type DebouncedResourceUpdater struct {
    decoratedUpdater ResourceUpdater
    namespaceChannels map[string]chan string
    period time.Duration
}

func (dru *DebouncedResourceUpdater) Update(namespace string) error {
    channel, found := dru.namespaceChannels[namespace]

    if !found {
        out := make(chan string)
        channel = make(chan string)

        Debounce(dru.period, channel, out)

        // Listening for output events
        go func(out chan string) {
            for range out {
                dru.decoratedUpdater.Update(namespace)
            }
        }(out)

        dru.namespaceChannels[namespace] = channel
    }

    channel <- namespace

    return nil
}

func Debounce(lull time.Duration, in chan string, out chan string) {
    go func() {

        var last int64 = 0

        for {
            select {
            case <-in:
                last = time.Now().Unix()

            case <-time.Tick(lull):
                if last != 0 && time.Now().Unix() >= last+int64(lull.Seconds()) {
                    last = 0
                    out <- ""
                }
            }
        }
    }()
}
