angular.module('continuousPipeRiver')
	.service('LogFinder', function($firebaseObject) {
		this.find = function(identifier) {
			return $firebaseObject(this.getReference(identifier));
		};

		this.getReference = function(identifier) {
			var root = firebase.database().ref();

			if (identifier.substr(0, 1) != '/') {
				root = root.child('logs');
			}

			return root.child(identifier);
		};
	});
