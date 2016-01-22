var ObjectId = require('mongodb').ObjectId;

var LogsCollection = function(db) {
    var collection = db.collection('logs');

    this.insert = function(log, callback) {
        collection.insertOne(log, {w: 1}, function(error) {
            callback(error);
        });
    };

    this.find = function(id, callback) {
        return collection.findOne({_id: ObjectId(id)}, function(error, log) {
            callback(error, log);
        });
    };
};

module.exports = LogsCollection;
