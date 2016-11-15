angular.module('continuousPipeRiver')
    .directive('followScroll', function() {
        return {
            link: function(scope, element, attributes) {
                var doScrollToBottom = function(element) {
                    element.scrollTop(element[0].scrollHeight);
                };
                var scrollToTheBottom = function() {
                    var dialog = $('md-dialog-content');
                    if (dialog.length) {
                        doScrollToBottom(dialog);
                    }

                    doScrollToBottom(element);
                };

                scope.$watch(attributes.followScroll, function(follow) {
                    element[follow ? 'on' : 'off']('updated-html', scrollToTheBottom);

                    if (follow) {
                        scrollToTheBottom();
                    }
                });
            },
            restrict: 'A'
        };
    });
