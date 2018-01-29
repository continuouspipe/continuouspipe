var md5 = require('md5');

module.exports = function(firebase, bucket) {
    this.fetch = function(identifier) {
        return bucket.read(this._archive_file_name(identifier)).then(function(contents) {
            return JSON.parse(contents);
        });
    }

    this.archiveReferences = function(object) {
        var promises = [],
            self = this;

        for (var property in object) {
            if (!object.hasOwnProperty(property)) {
                continue;
            }

            if (typeof object[property] == "object") {
                promises.push(self.archiveReferences(object[property]).then(
                    (function(property) {
                        return function(value) {
                            return object[property] = value;
                        };
                    })(property)
                ));
            }
        }

        return Promise.all(promises).then(function() {
            if (object.type != 'raw' || !object.path) {
                return Promise.resolve(object);
            }

            var identifier = typeof object.path == 'string' ? object.path : object.path.identifier;

            return self.archive(identifier);
        });
    }

    this.archive = function(identifier) {
        var self = this;

        return firebase.read(identifier).then(function(value, err) {
            if (!value) {
                return Promise.reject(new Error('Log "'+identifier+'" was not found'));
            }

            // Return the log directly when already archived
            if (value.archived) {
                return value;
            }

            return self.archiveReferences(value).then(function(withArchivedReferencesValue) {
                return bucket.write(self._archive_file_name(identifier), JSON.stringify(value)).then(function(publicFilePath) {
                    return firebase.write(identifier, {
                        _id: identifier,
                        archived: true,
                        archive: publicFilePath
                    });
                });
            })
        });
    }

    this._archive_file_name = function(identifier) {
        return md5(identifier)+'.json';
    };
}
