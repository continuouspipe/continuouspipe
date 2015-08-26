angular.module('logstream')
    .service('LogRepository', ['$meteor', function($meteor) {
        this.findByParentId = function(id) {
            $meteor.subscribe('logChildren', id);

            return $meteor.collection(function() {
                return Logs.find({
                    parent: id
                });
            })
        };
    }]);
