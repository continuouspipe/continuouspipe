var k8s = require('kubernetes-client'),
    google = require('googleapis');

module.exports = new function() {
    this.auth = function(credentials) {
        if (credentials.google_cloud_service_account) {
            return new Promise(function(resolve, reject) {
                var jsonKey = JSON.parse(Buffer.from(credentials.google_cloud_service_account, 'base64'));
                var jwtClient = new google.auth.JWT(
                    jsonKey.client_email,
                    null,
                    jsonKey.private_key,
                    ['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/cloud-platform', 'https://www.googleapis.com/auth/appengine.admin', 'https://www.googleapis.com/auth/compute'],
                    null
                );

                jwtClient.authorize(function (err, token) {
                    if (err) {
                        reject(err);
                    }

                    resolve({
                        bearer: token.access_token
                    });
                });
            });
        } else {
            return Promise.resolve({
                user: credentials.username,
                pass: credentials.password
            });
        }
    };

    this.createClientFromCluster = function(cluster) {
        // Add BC if no cluster credentials
        if (!cluster.credentials) {
            cluster.credentials = {
                username: cluster.username,
                password: cluster.password
            }
        }

        return this.auth(cluster.credentials).then(function(auth) {
            console.log(auth);

            return Promise.resolve(new k8s.Core({
                url: cluster.address,
                version: 'v1',
                auth: auth,
                request: {
                    strictSSL: false
                }
            }));
        });
    }
};
