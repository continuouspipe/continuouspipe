'use strict';

angular.module('continuousPipeRiver')
    .directive('logFrame', function(LOG_STREAM_URL) {
        return {
            restrict: 'E',
            scope: {
                log: '='
            },
            template: '<iframe class="log-frame"></iframe>',
            link: function(scope, element) {
                $('iframe', element).attr('src', LOG_STREAM_URL+'/#/log/'+scope.log).attr('allowtransparency', 'true');

                setTimeout(function() {
                    $('iframe', element).iFrameResize({
                        heightCalculationMethod: 'lowestElement'
                    });
                }, 500);
            }
        };
    });
