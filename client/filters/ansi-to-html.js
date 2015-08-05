angular.module('logstream')
    .filter('ansiToHtml', ['$sce', function($sce) {
        return function (value) {
            return $sce.trustAsHtml(ansi_up.ansi_to_html(value));
        };
    }]);
