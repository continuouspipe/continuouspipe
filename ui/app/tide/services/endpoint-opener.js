'use strict';

angular.module('continuousPipeRiver')
    .service('EndpointOpener', function($mdDialog) {
        this.open = function(endpoint) {
            var confirm = $mdDialog.confirm()
              .title('This will open the endpoint "'+endpoint.name+'"')
              .textContent('The address you will be redirected to ('+endpoint.address+') is outside of ContinuousPipe and will work on your browser only it is answers to HTTP.')
              .ok('Open it!')
              .cancel('Cancel');

            $mdDialog.show(confirm).then(function() {
                window.open('http://'+endpoint.address, '_blank');
            });
        };
    })
;