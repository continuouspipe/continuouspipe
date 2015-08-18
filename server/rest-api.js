var Api = new Restivus({
    useDefaultAuth: true,
    prettyJson: true
});

// Generates: GET, POST on /api/logs and GET, PUT, DELETE on
// /api/logs/:id for Items collection
Api.addCollection(Logs, {
    endpoints: {
        post: {
            action: function() {
                var id = Logs.insert(this.bodyParams, {autoConvert: false}),
                    entity = Logs.findOne(id);

                if (entity) {
                    return {
                        statusCode: 201,
                        body: {
                            status: 'success',
                            data: entity
                        }
                    };
                }

                return {
                    statusCode: 400,
                    body: {
                        status: 'fail',
                        message: 'No item added'
                    }
                };
            }
        },
        put: {
            action: function() {
                var objectIdentifier = this.urlParams.id,
                    existingObject = Logs.findOne({_id: objectIdentifier}),
                    objectChanges = this.bodyParams;

                if (!objectChanges._id) {
                    objectChanges._id = objectIdentifier;
                }

                // Get the different between the 2 objects
                var diff = jsDiff2Mongo(existingObject, this.bodyParams),
                    patch = diff[1];

                // We want to apply and differential PUT, so remove the fields missing on the body
                delete patch.$unset;

                var isUpdated = Logs.update(objectIdentifier, patch, {
                    autoConvert: false
                });
                if (isUpdated) {
                    return {
                        status: 'success',
                        data: Logs.findOne({_id: objectIdentifier})
                    };
                }

                return {
                    statusCode: 400,
                    body: 'Unable to apply update'
                };
            }
        }
    }
});

Meteor.startup(function () {
    Meteor.publish('logChildren', function (id) {
        return Logs.find({
            parent: id
        });
    });
});
