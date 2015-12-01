Logs = new Mongo.Collection('logs');
Logs.attachSchema(new SimpleSchema({
    type: {
        type: String
    },
    contents: {
        type: String,
        optional: true,
        trim: false
    },
    status: {
        type: String,
        optional: true
    },
    parent: {
        type: String,
        optional: true
    },
    columns: {
        type: [String],
        optional: true
    },

    // Force value to be current date (on server) upon insert
    // and prevent updates thereafter.
    createdAt: {
        type: Date,
        autoValue: function() {
            if (this.isInsert) {
                return new Date;
            } else if (this.isUpsert) {
                return {$setOnInsert: new Date};
            } else {
                this.unset();
            }
        }
    },

    // Force value to be current date (on server) upon update
    // and don't allow it to be set upon insert.
    updatedAt: {
        type: Date,
        autoValue: function() {
            if (this.isUpdate) {
                return new Date();
            }
        },
        denyInsert: true,
        optional: true
    }
}));

LogRepository = {
    find: function(id) {
        return Logs.findOne(id);
    },
    insert: function(log) {
        var id = Logs.insert(log, {autoConvert: false});

        return Logs.findOne(id);
    },
    update: function(objectIdentifier, log) {
        var existingObject = Logs.findOne({_id: objectIdentifier});
        if (!existingObject) {
            throw new Error('Log #'+objectIdentifier+' do not exists');
        }

        if (!log._id) {
            log._id = objectIdentifier;
        }

        // Get the different between the 2 objects
        var diff = jsDiff2Mongo(existingObject, log),
            patch = diff[1];

        // We want to apply and differential PUT, so remove the fields missing on the body
        delete patch.$unset;

        return Logs.update(objectIdentifier, patch, {
            autoConvert: false
        });
    },
    remove: function(id) {
        // Remove children
        Logs.find({
            parent: id
        }).forEach(function(result) {
            LogRepository.remove(result._id);
        });

        // Remove the log itself
        Logs.remove({_id: id});
    },
    findChildren: function(id) {
        return Logs.find({
            parent: id
        }).fetch();
    }
};

Meteor.methods({
    'log.find': function(id) {
        var found = LogRepository.find(id);
        if (found === undefined) {
            return {status: 404};
        }

        return {status: 200, log: found};
    },
    'log.insert': LogRepository.insert,
    'log.update': LogRepository.update,
    'log.remove': LogRepository.remove,
    'log.children': LogRepository.findChildren
});
