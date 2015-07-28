Logs = new Mongo.Collection('logs');

if (Meteor.isClient) {
    angular.module('logstream', [
        'angular-meteor',
        'ui.router',
        'RecursionHelper'
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

    angular.module('logstream').directive('logs', ['RecursionHelper', function(RecursionHelper) {
        return {
            restrict: 'E',
            scope: {
                logId: '='
            },
            templateUrl: 'client/logs/logs.ng.html',
            controller: function ($scope, $meteor) {
                $scope.toggleChildrenDisplay = function(log) {
                    log.displayChildren = !log.displayChildren;
                };

                $scope.logs = $meteor.collection(function() {
                    return Logs.find({
                        parent: $scope.logId
                    });
                });
            },
            compile: function(element) {
                return RecursionHelper.compile(element);
            }
        }
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
