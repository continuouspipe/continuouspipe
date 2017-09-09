var md5 = require('md5');

module.exports = function(firebase, bucket) {
    this.fetch = function(identifier) {
        return bucket.read(this._archive_file_name(identifier)).then(function(contents) {
            return JSON.parse(contents);
        });
    }

    this.archive = function(identifier) {
        var self = this;

        return firebase.read(identifier).then(function(value) {
            // Return the log directly when already archived
            if (value.archived) {
                return value;
            }

            return bucket.write(self._archive_file_name(identifier), JSON.stringify(value)).then(function(publicFilePath) {
                return firebase.write(identifier, {
                    _id: identifier,
                    archived: true,
                    archive: publicFilePath
                });
            });
        });
    }

    this._archive_file_name = function(identifier) {
        return md5(identifier)+'.json';
    };
}
