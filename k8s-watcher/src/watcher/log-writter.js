module.exports = function(firebaseEntry) {
    this.write = function(contents) {
        firebaseEntry.push({
            type: 'text',
            contents: contents,
        });
    };
};
