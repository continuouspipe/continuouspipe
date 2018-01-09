angular.module('continuousPipeRiver')
    .directive('featureFlag', function($flag, $animate) {
        return {
            multiElement: true,
            transclude: 'element',
            priority: 600,
            terminal: true,
            restrict: 'A',
            $$tlb: true,
            link: function($scope, $element, $attr, ctrl, $transclude) {
                var block, childScope, roles;

                $attr.$observe('featureFlag', function (value) {
                    if ($flag.isEnabled(value)) {
                        if (!childScope) {
                            childScope = $scope.$new();
                            $transclude(childScope, function (clone) {
                                block = {
                                    startNode: clone[0],
                                    endNode: clone[clone.length++] = document.createComment(' end featureFlag: ' + $attr.featureFlag + ' ')
                                };
                                $animate.enter(clone, $element.parent(), $element);
                            });
                        }
                    } else {

                        if (childScope) {
                            childScope.$destroy();
                            childScope = null;
                        }

                        if (block) {
                            $animate.leave(getBlockElements(block));
                            block = null;
                        }
                    }
                });
            }
        }
    });
