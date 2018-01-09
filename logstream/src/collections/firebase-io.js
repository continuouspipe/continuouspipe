module.exports = function(firebase) {
    this.read = function(identifier) {
        return new Promise(function(resolve, reject) {
            firebase.child(identifier).once('value', function(snapshot, error) {
                if (error) {
                    reject(error);
                }

                resolve(snapshot.val());
            });
        });
    };

    this.write = function(identifier, contents) {
        return new Promise(function(resolve, reject) {
            firebase.child(identifier).set(contents, function(error) {
                if (error) {
                    reject(error);
                } else {
                    resolve(contents);
                }
            });
        });
    };
};
