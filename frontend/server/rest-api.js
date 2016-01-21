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
                var entity = LogRepository.insert(this.bodyParams);

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
                    isUpdated = LogRepository.update(objectIdentifier, this.bodyParams);

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
