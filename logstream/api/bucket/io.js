module.exports = function(bucket) {
    this.read = function(fileName) {
        return new Promise(function(resolve, reject) {
            var chunks = [];

            bucket.file(fileName)
                .createReadStream()
                .on('data', function(response){
                    chunks.push(response.toString());
                })
                .on('end', function(){
                    var contents = chunks.join('');

                    resolve(contents);
                })
                .on('error', function(error) {
                    reject(error);
                });
        });
    };

    this.write = function(fileName, contents) {
        return new Promise(fileName(resolve, reject) {
            bucket.file(file)
                .createWriteStream()
                .on('error', function (error) {
                    reject(error);
                })
                .on('finish', function () {
                    resolve('https://storage.googleapis.com/'+bucket.id+'/'+fileName);
                })
                .end(contents);
        });
    };
};
