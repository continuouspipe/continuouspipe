Logs = new Mongo.Collection('logs');

if (Meteor.isClient) {
    angular.module('logstream', [
        'angular-meteor'
    ]);

    angular.module('logstream').controller('LogsCtrl', ['$scope', '$meteor', function ($scope, $meteor) {
        $scope.logs = $meteor.collection(Logs);
    }]);
}

if (Meteor.isServer) {
    Meteor.startup(function () {
        // code to run on server at startup
    });
}
