'use strict';

angular.module('continuousPipeRiver')
    .directive('floatInput', function() {
        return {
            transclude: true,
            template: '<div class="fg-line" ng-transclude></div><label class="fg-label">{{ name }}</label>',
            scope: {
                name: '@',
                watcher: '='
            },
            restrict: 'E',
            link: function(scope, element, attributes) {
                element.on('focus', '.fg-line', function(){
                    $(this).closest('.fg-line').addClass('fg-toggled');
                });

                element.on('blur', '.fg-line', function(){
                    var p = $(this).closest('.form-group');
                    var i = p.find('.form-control').val();

                    if (p.hasClass('fg-float')) {
                        if (i.length == 0) {
                            $(this).closest('.fg-line').removeClass('fg-toggled');
                        }
                    }
                    else {
                        $(this).closest('.fg-line').removeClass('fg-toggled');
                    }
                });

                element.parent().each(function(){
                    var i = $(this).val();

                    if (!i.length == 0) {
                        $(this).closest('.fg-line').addClass('fg-toggled');
                    }
                });

                if (attributes.watcher) {
                    scope.$watch('watcher', function(value) {
                        var fgLine = $('.fg-line', element);

                        if (value) {
                            fgLine.addClass('fg-toggled');
                        } else {
                            fgLine.removeClass('fg-toggled');
                        }
                    });
                }
            }
        };
    });
