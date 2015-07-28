Logs = new Mongo.Collection('logs');

if (Meteor.isClient) {
    angular.module('logstream', [
        'angular-meteor',
        'ui.router'
    ]).config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider) {
        $urlRouterProvider.otherwise("/");

        $stateProvider
            .state('home', {
                url: '/',
                templateUrl: 'client/home.ng.html'
            })
            .state('logs', {
                url: '/log/:logId',
                templateUrl: 'client/log.ng.html',
                controller: 'LogCtrl'
            })
        ;
    }]);

    angular.module('logstream').controller('LogCtrl', ['$scope', '$meteor', '$stateParams', function ($scope, $meteor, $stateParams) {
        $scope.logId = $stateParams.logId;
//        $scope.logs = $meteor.collection(Logs);
    }]);
}

if (Meteor.isServer) {
    Meteor.startup(function () {
        // code to run on server at startup
    });
}
