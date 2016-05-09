angular.module('logstream')
    .config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider) {
        $urlRouterProvider.otherwise("/");

        $stateProvider
            .state('home', {
                url: '/',
                templateUrl: 'client/views/home.ng.html'
            })
            .state('logs', {
                url: '/log/:logId',
                templateUrl: 'client/views/log.ng.html',
                controller: 'LogCtrl'
            })
        ;
    }]);
