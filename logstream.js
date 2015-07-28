if (Meteor.isClient) {
    // This code only runs on the client
    angular.module('logstream',['angular-meteor']);

    angular.module('logstream').controller('LogsCtrl', ['$scope',
        function ($scope) {
            $scope.logs = [
                { text: 'This is task 1' },
                { text: 'This is task 2' },
                { text: 'This is task 3' }
            ];
        }]);
}

if (Meteor.isServer) {
  Meteor.startup(function () {
    // code to run on server at startup
  });
}
