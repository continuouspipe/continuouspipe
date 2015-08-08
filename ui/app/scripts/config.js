'use strict';

angular.module('continuousPipeRiver')
    .constant('RIVER_API_URL', 'http://continuouspipe_river.docker/app_dev.php')
    .constant('AUTHENTICATOR_API_URL', 'http://continuouspipe_authenticator.docker/app_dev.php')
    .constant('LOG_STREAM_URL', 'http://logstream.docker')
;
