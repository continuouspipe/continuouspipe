const sinon = require('sinon')
const LogWritter = require('../../src/watcher/log-writter');
const LogsWatcher = require('../../src/watcher/logs-watcher');
const Stream = require('stream');
const Table = require('cli-table');

describe('LogWritter: write logs to Firebase', () => {
    var writer, podStream, kubernetesClient;

    beforeEach(() => {
        writer = new InMemoryWritter();
        podStream = new Stream();
        kubernetesClient = new KubernetesClient({
            namespaces: {
                myNamespace: {
                    pods: {
                        myPod: {
                            metadata: {
                                name: 'myPod'
                            },
                            stream: podStream
                        },
                        outOfMemoryPod: {
                            metadata: {
                                name: 'outOfMemoryPod'
                            },
                            stream: podStream,
                            status: outOfMemoryPodStatus
                        }
                    }
                }
            }
        });
    })

    it('writes the logs from the pod', () => {
        var watcher = new LogsWatcher(
            kubernetesClient,
            {namespace: 'myNamespace', pod: 'myPod'},
            writer
        );

        return watcher.watch().then(function() {
            podStream.emit('data', 'Try');
            podStream.emit('close');            
        }).then(function() {
            assertStartsWith('Try[stream closed]', writer.read());
        })
    })

    it('write the pod status when the stream is closed', () => {
        var watcher = new LogsWatcher(
            kubernetesClient,
            {namespace: 'myNamespace', pod: 'outOfMemoryPod'},
            writer
        );

        return watcher.watch().then(function() {
            podStream.emit('data', '');
            podStream.emit('close');
        }).then(function() {
            var expectedTable = new Table({ 
                head: ["Container", "Ready?", "Last state", "Reason"] 
            });

            expectedTable.push(['mssql', 'No', 'terminated', 'OOMKilled']);

            assertEquals(
                '[stream closed]'+"\n"+expectedTable.toString(), 
                writer.read()
            );
        });
    })
    // it('write the pod status when the pod is not running at the first place')
})

function KubernetesNamespacedPodClient(configuration) {
    this.get = function(podName, callback) {
        if (podName in configuration) {
            callback(null, configuration[podName]);
        } else {
            callback(new Error('Pod not found'), null);
        }
    }

    this.log = function({ name }) {
        if (name in configuration && configuration[name].stream) {
            return configuration[name].stream;
        }

        return null;
    }
}

function KubernetesNamespaceClient(configuration) {
    this.po = new KubernetesNamespacedPodClient(configuration.pods);
}

function KubernetesClient(configuration) {
    this.ns = function(namespace) {
        return new KubernetesNamespaceClient(configuration.namespaces[namespace]);
    }
}

function InMemoryWritter() {
    var buffer = '';

    this.write = function(data) {
        buffer += data;
    }

    this.read = function() {
        return buffer;
    }
}

function assertEquals(expected, actual) {
    if (expected != actual) {
        sinon.assert.fail('Got this instead: '+actual);
    }
}

function assertStartsWith(start, actual) {
    if (actual.substr(0, start.length) != start) {
        sinon.assert.fail('Got this instead: '+actual);
    }
}

var outOfMemoryPodStatus = {
    "conditions": [
        {
            "lastProbeTime": null,
            "lastTransitionTime": "2017-11-08T17:04:49Z",
            "status": "True",
            "type": "Initialized"
        },
        {
            "lastProbeTime": null,
            "lastTransitionTime": "2017-11-08T17:48:00Z",
            "message": "containers with unready status: [mssql]",
            "reason": "ContainersNotReady",
            "status": "False",
            "type": "Ready"
        },
        {
            "lastProbeTime": null,
            "lastTransitionTime": "2017-11-08T17:04:49Z",
            "status": "True",
            "type": "PodScheduled"
        }
    ],
    "containerStatuses": [
        {
            "containerID": "docker://2aee8da8a673f63e4c4916da2a906c227eeed07638b0ba29fcda60f467db5667",
            "image": "microsoft/mssql-server-linux:2017-GA",
            "imageID": "docker-pullable://microsoft/mssql-server-linux@sha256:77ebcec549076994f93ab85c5ce194e85366d9bcd124c53e1347660edd315666",
            "lastState": {
                "terminated": {
                    "containerID": "docker://2aee8da8a673f63e4c4916da2a906c227eeed07638b0ba29fcda60f467db5667",
                    "exitCode": 0,
                    "finishedAt": "2017-11-08T17:48:00Z",
                    "reason": "OOMKilled",
                    "startedAt": "2017-11-08T17:47:54Z"
                }
            },
            "name": "mssql",
            "ready": false,
            "restartCount": 13,
            "state": {
                "waiting": {
                    "message": "Back-off 5m0s restarting failed container=mssql pod=mssql-9bfb9d676-qg4n7_bce7e8e2-c487-11e7-9c58-0a580a84036d-try-too-low-resources(ee31c127-c4a6-11e7-a192-42010af002e3)",
                    "reason": "CrashLoopBackOff"
                }
            }
        }
    ],
    "hostIP": "10.0.0.3",
    "phase": "Running",
    "podIP": "10.60.9.17",
    "qosClass": "Guaranteed",
    "startTime": "2017-11-08T17:04:49Z"
};
