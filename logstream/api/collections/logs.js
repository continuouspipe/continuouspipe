var uuid = require('node-uuid'),
    md5 = require('md5');

var LogsCollection = function(root, bucket) {
    this.insert = function(log, callback) {
        var path = log.parent ? log.parent+'/children' : null,
            logRoot = path ? root.child(path) : root,
            child = logRoot.push();

        log.updatedAt = new Date();
        log.createdAt = new Date();

        child.set(log, function(error) {
            log._id = path !== null ? path+'/'+child.key() : child.key();

            callback(error, log);
        });
    };

    this.update = function(id, updatedProperties, callback) {
        var child = root.child(id);

        updatedProperties.updatedAt = new Date();

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
        root.child(id).once('value', function(snapshot, error) {
            if (error) {
                callback(error);
            }

            var value = snapshot.val(),
                file = this._getArchiveFileName(id);

            // Return the log directly when already archived
            if (value.archived) {
                return callback(null, value);
            }

            bucket.file(file)
                .createWriteStream()
                .on('error', function (error) {
                    callback(error);
                })
                .on('finish', function () {
                    var archivedLog = {
                        _id: id,
                        archived: true,
                        archive: 'https://storage.googleapis.com/'+bucket.id+'/'+file
                    };

                    root.child(id).set(archivedLog, function(error) {
                        callback(error, archivedLog);
                    });
                })
                .end(JSON.stringify(value));
        }.bind(this));
    };

    this.fetchArchive = function(id, callback) {
        var chunks = [];

        bucket.file(this._getArchiveFileName(id))
            .createReadStream()
            .on('data', function(response){
                chunks.push(response.toString());
            })
            .on('end', function(){
                var contents = chunks.join('');

                callback(null, JSON.parse(contents));
            })
            .on('error', function(error) {
                callback(error);
            })
    };

    this._getArchiveFileName = function(id) {
        return md5(id)+'.json';
    };
};

module.exports = LogsCollection;
