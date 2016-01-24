var ObjectId = require('mongodb').ObjectId;

var LogsCollection = function(db) {
    var collection = db.collection('logs');

    this.insert = function(log, callback) {
        if (!log._id) {
            log._id = (new ObjectId()).toHexString();
        }

        log.createdAt = new Date();
        log.updatedAt = new Date();

        collection.insertOne(log, {w: 1}, function(error) {
            callback(error);
        });
    };

    this.find = function(id, callback) {
        return collection.findOne({_id: id}, function(error, log) {
            callback(error, log);
        });
    };

    this.update = function(id, updatedProperties, callback) {
        updatedProperties.updatedAt = new Date();

        return collection.updateOne({_id: id}, {
            $set: updatedProperties
        }, function(error, result) {
            if (error === null && result.result.nModified != 1) {
                callback(new Error(result.result.nModified+' updated logs instead of 1'));
            } else {
                callback(error, result);
            }
        });
    };
};

module.exports = LogsCollection;
