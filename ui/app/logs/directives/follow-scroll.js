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

                    var tab = $('md-tab-content.md-active > div');
                    if (tab.length) {
                        doScrollToBottom(tab);
                    }

                    if(element[0].parentNode.classList.contains('fullscreen')) {
                        var newElem = element[0].parentNode;
                        doScrollToBottom($(newElem));
                        return;                
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
