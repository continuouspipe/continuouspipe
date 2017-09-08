angular.module('continuousPipeRiver')
	.service('LogFinder', function($firebaseObject, $firebaseDatabaseResolver) {
		this.find = function(descriptor) {
			return this.getReference(descriptor).then(function(reference) {
				return $firebaseObject(reference);
			});
		};

		this.getReference = function(descriptor) {
			if (typeof descriptor == 'string') {
                descriptor = {
                	identifier: descriptor
				};
			}

			return $firebaseDatabaseResolver.get(descriptor.database).then(function(database) {
                var root = database.ref();

                // Backward compatibility
                if (descriptor.identifier.substr(0, 1) != '/') {
                    root = root.child('logs');
                }

                return root.child(descriptor.identifier);
			});
		};
	});
