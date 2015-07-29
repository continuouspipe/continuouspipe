angular.module('logstream')
    .service('LogRepository', function($meteor) {
        this.findByParentId = function(id) {
            return $meteor.collection(function() {
                return Logs.find({
                    parent: id
                });
            })
        };
    });
