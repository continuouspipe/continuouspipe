var Api = new Restivus({
    useDefaultAuth: true,
    prettyJson: true
});

// Generates: GET, POST on /api/logs and GET, PUT, DELETE on
// /api/logs/:id for Items collection
Api.addCollection(Logs);

Meteor.startup(function () {

});
