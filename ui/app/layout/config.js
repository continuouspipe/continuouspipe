'use strict';

angular.module('continuousPipeRiver')
    .config(function(cfpLoadingBarProvider) {
        cfpLoadingBarProvider.includeSpinner = false;
    });
