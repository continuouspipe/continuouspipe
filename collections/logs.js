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
