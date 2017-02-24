'use strict';

angular.module('continuousPipeRiver')
    .service('$aside', function ($rootScope) {
        var self = this;

        this.showSideBar = function () {
            $rootScope.aside = true;
        };

        this.hideSideBar = function () {
            $rootScope.aside = false;
        };

        $rootScope.toggleSideBar = function () {
            $rootScope.aside = !$rootScope.aside;
        };

        this.screenSizeDefault = function () {
            if ($(window).width() >= 900) {
                self.showSideBar();
            } else {
                self.hideSideBar();
            }
        };
    })
    .run(function ($rootScope, $aside) {
        $rootScope.$on('$stateChangeSuccess', function (event, toState) {
            $rootScope.showToggle = toState.aside;

            if (toState.aside) {
                $aside.screenSizeDefault();

                $(window).resize(function () {
                    $aside.screenSizeDefault();
                });
            } else {
                $aside.hideSideBar();
            }
        });
    });
