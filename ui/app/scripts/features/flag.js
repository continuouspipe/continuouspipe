angular.module('continuousPipeRiver')
    .service('$flag', function($injector) {
        this.isEnabled = function(flag) {
            var configuration = $injector.get(flag.toUpperCase()+'_ENABLED');

            return configuration === 'true';
        }
    });
