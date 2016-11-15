var k8s = require('kubernetes-client');

module.exports = new function() {
    this.createClientFromCluster = function(cluster) {
        return new k8s.Core({
            url: cluster.address,
            version: cluster.version,
            auth: {
                user: cluster.username,
                pass: cluster.password
            },
            request: {
                strictSSL: false
            }
        });
    }
};
