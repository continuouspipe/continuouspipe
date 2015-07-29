Logs = new Mongo.Collection('logs');
Logs.attachSchema(new SimpleSchema({
    contents: {
        type: String
    },
    parent: {
        type: String,
        optional: true
    }
}));
