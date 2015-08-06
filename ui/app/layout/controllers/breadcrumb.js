'use strict';

angular.module('continuousPipeRiver')
    .controller('BreadcrumbController', function($scope, $breadcrumb) {
        $scope.links = $breadcrumb.links;
    });
