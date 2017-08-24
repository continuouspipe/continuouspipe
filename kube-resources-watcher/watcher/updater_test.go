package watcher

import (
    "testing"
    "time"
)

type CountedCalledUpdater struct {
    Calls map[string]int
}
func (dru* CountedCalledUpdater) Update(namespace string) error {
    dru.Calls[namespace]++

    return nil
}

func TestDebouncedUpdaterIsNotCallingMoreThanExpected(t *testing.T) {
    countedUpdater := &CountedCalledUpdater{
        Calls: map[string]int{},
    }
    updater := NewDebouncedResourceUpdater(countedUpdater, 500 * time.Millisecond)
    updater.Update("namespace")
    updater.Update("namespace")
    updater.Update("second-namespace")
    updater.Update("namespace")
    updater.Update("namespace")

    time.Sleep(time.Second * 1)

    if countedUpdater.Calls["namespace"] != 1 {
        t.Errorf("Expected one call for 'namespace', got %d", countedUpdater.Calls["namespace"])
    }

    if countedUpdater.Calls["second-namespace"] != 1 {
        t.Errorf("Expected one call for 'second-namespace', got %d", countedUpdater.Calls["namespace"])
    }
}
