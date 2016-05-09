var uuid = require('node-uuid');

var LogsCollection = function(root) {
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
};

module.exports = LogsCollection;
