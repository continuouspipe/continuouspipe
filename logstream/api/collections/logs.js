var uuid = require('node-uuid'),
    CONTENTS_BYTES_LIMIT = 10485760,
    Raven = require('raven'),
    Archiver = require('./archiver'),
    Firebase = require('./firebase-io'),
    Bucket = require('./bucket/io');

var LogsCollection = function(root, bucket) {
    var archiver = new Archiver(
        new Firebase(root), 
        new Bucket(bucket)
    );

    this.insert = function(log, callback) {
        var path = log.parent ? log.parent+'/children' : null,
            logRoot = path ? root.child(path) : root,
            child = logRoot.push();

        // If the contents is too long, do batch insert
        if (log && log.contents && Buffer.byteLength(log.contents, 'utf8') > CONTENTS_BYTES_LIMIT) {
            var buffer = Buffer.from(log.contents);
            log.contents = buffer.toString('utf8', 0, CONTENTS_BYTES_LIMIT);

            return this.insert(log, function(error) {
                if (error) {
                    return callback(error);
                }

                log.contents = buffer.toString('utf8', CONTENTS_BYTES_LIMIT);

                return this.insert(log, callback);
            });
        }

        try {
            child.set(log, function (error) {
                log._id = path !== null ? path + '/' + child.key() : child.key();

                callback(error, log);
            });
        } catch (e) {
            Raven.captureException(e);

            // Silently fail as it shouldn't
            callback(null, log);
        }
    };

    this.update = function(id, updatedProperties, callback) {
        var child = root.child(id);

        try {
            child.update(updatedProperties, function(error) {
                if (error) {
                    return callback(error);
                }

                child.once('value', function(snapshot) {
                    var value = snapshot.val();
                    value._id = id;

                    callback(null, value);
                });
            });
        } catch (e) {
            Raven.captureException(e);

            // Silently fail as it shouldn't
            updatedProperties._id = id;
            callback(null, updatedProperties);
        }
    };

    this.fetch = function(id, callback) {
        root.child(id).once('value', function(snapshot, error) {
            if (error) {
                callback(error);
            }

            var log = snapshot.val();

            if (log.archived) {
                return this.fetchArchive(id, callback);
            }

            return callback(null, log);
        }.bind(this));
    };

    this.archive = function(id, callback) {
        archiver.archive(id).then(function(archivedLog) {
            callback(null, archivedLog);
        }, function(error) {
            callback(error);
        });
    };

    this.fetchArchive = function(id, callback) {
        archiver.fetch(id).then(function(contents) {
            callback(null, contents);
        }, function(error) {
            callback(error);
        });
    };
};

module.exports = LogsCollection;
