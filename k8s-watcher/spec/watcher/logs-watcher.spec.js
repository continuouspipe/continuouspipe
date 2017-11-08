const sinon = require('sinon')
const LogWritter = require('../../src/watcher/log-writter');
const LogsWatcher = require('../../src/watcher/logs-watcher');
const Stream = require('stream');

describe('LogWritter: write logs to Firebase', () => {
    var watcher, writer, podStream;

    beforeEach(() => {
        writer = new InMemoryWritter();
        podStream = new Stream();
        watcher = new LogsWatcher(
            new KubernetesClient({
                namespaces: {
                    myNamespace: {
                        pods: {
                            myPod: {
                                metadata: {
                                    name: 'myPod'
                                },
                                stream: podStream
                            }
                        }
                    }
                }
            }),
            {
                namespace: 'myNamespace',
                pod: 'myPod'
            },
            writer
        )
    })

    it('writes the logs from the pod', () => {
        watcher.watch();

        podStream.emit('data', 'Try');
        podStream.emit('close');

        assertEquals('Try[stream closed]', writer.read());
    })

    // it('write the pod status when the stream is closed')
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

