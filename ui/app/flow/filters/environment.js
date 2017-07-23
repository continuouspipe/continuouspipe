angular.module('continuousPipeRiver')
    .filter('environmentName', function() {
        return function(environment, flow) {
            if (!environment) {
                return '';
            }

            var flowUuidPrefix = flow ? flow.uuid+'-' : '';

            return environment.identifier.indexOf(flowUuidPrefix) === 0 ?
                environment.identifier.substr(flowUuidPrefix.length) :
                environment.identifier;
        }
    })
;
