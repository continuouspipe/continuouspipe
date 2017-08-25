# Redis HA (Highly Available)

In order to prevent any downtime, we are going to use Redis in HA mode with 3 replicas on the staging and production
environments.

## Setup

[From the official HA Redis example](https://github.com/kubernetes/kubernetes/tree/master/examples/storage/redis).

We have customised the image via [ContinuousPipe Dockerfiles](https://github.com/continuouspipe/dockerfiles/pull/264) to
support failover quicker than 60 seconds.
Magento without it's config cache is non-functional, so the smallest amount of downtime is preferred.

```
# Create a bootstrap master
kubectl --namespace=foo create -f redis-master.yml

# Create a service to track the sentinels
kubectl --namespace=foo create -f redis-sentinel-service.yml

# Create a StatefulSet for redis sentinels
kubectl --namespace=foo create -f redis-sentinel-statefulset.yml

# Create a headless service to return all sentinels in the StatefulSet
kubectl --namespace=foo create -f redis-sentinel-service-headless.yml

# Create a redis service if you don't have one yet
kubectl --namespace=foo create -f redis-service.yml

# Create a StatefulSet for redis servers - note if code to support sentinel clusters isn't present, edit this file to only have 1 replica
kubectl --namespace=foo create -f redis-statefulset.yml

# Delete the original master pod
kubectl --namespace=foo delete pods redis-master
```

## Persistent data

The Redis data is stored into `emptyDir` volume, that are available for the lifetime of the pod on a dedicated node. They are
not backed by a persistent volume on GCE. The reason for it is that Redis is only used as a cache and the HA mode should be
sufficient enough to keep the data (ie the application cache) regardless of random node failures.

## Recovery

We found ourselves in a situation whilst deploying this where the sentinels could not talk to any redis instance.
Deleting sentinel instances to try to help recovery did not help.
As sentinels do not start unless they have other sentinels to talk to to inform of the current master, (see https://github.com/kubernetes/kubernetes/blob/f21ee1a6a88cd145ba18a12d451dc309684cebd4/examples/storage/redis/image/run.sh#L26-L40 ).
we had to get a bit creative:

1. Choose a redis instance to become master, if there isn't one already:
```
kubectl --namespace=strathberry-staging get pods | grep redis
```

2. Describe the redis pod to find it's IP address
```
kubectl --namespace=strathberry-staging describe pod redis-random-chars
```

3. Exec on to the redis pod to tell it is the master
```
kubectl --namespace=strathberry-staging exec -it redis-random-chars bash

redis-cli ROLE
redis-cli SLAVEOF NO ONE
redis-cli ROLE
```

3. Exec on to a sentinel that hasn't started and create a configuration manually, setting the IP of Master to be the one you chose as master:

```
kubectl --namespace=strathberry-staging exec -it redis-sentinel-1 bash

sentinel_conf=sentinel.conf

echo "sentinel monitor mymaster <IP-Of-Master> 6379 2" > ${sentinel_conf}
echo "sentinel down-after-milliseconds mymaster 60000" >> ${sentinel_conf}
echo "sentinel failover-timeout mymaster 180000" >> ${sentinel_conf}
echo "sentinel parallel-syncs mymaster 1" >> ${sentinel_conf}
echo "bind 0.0.0.0" >> ${sentinel_conf}

redis-sentinel ${sentinel_conf}
```

4. Watch the logs of containers, they should start reaching a consensus.

## Migrating from ReplicationController to StatefulSet

1. kubectl --namespace=strathberry-hotfix-set-mage-mode-in-env-php create -f infrastructure/redis/redis-statefulset.yml
2. kubectl --namespace=strathberry-hotfix-set-mage-mode-in-env-php scale rc redis --replicas=1
