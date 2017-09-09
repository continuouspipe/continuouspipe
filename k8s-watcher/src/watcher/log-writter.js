module.exports = function(firebaseEntry) {
    var throttle = function(func, limit) {
        var inThrottle,
        lastFunc,
        lastRan;

        return function() {
            var context = this,
            args = arguments;
            if (!inThrottle) {
                func.apply(context, args);
                lastRan = Date.now()
                inThrottle = true;
            } else {
                clearTimeout(lastFunc)
                lastFunc = setTimeout(function() {
                    if ((Date.now() - lastRan) >= limit) {
                        func.apply(context, args)
                        lastRan = Date.now()
                    }
                }, limit - (Date.now() - lastRan))
            }
        };
    };


    this.buffer = '';
    this.write = function(contents) {
        this.buffer += contents;
        this.send();
    };

    this.send = throttle(function() {
        this.writeToFirebase();
    }, 100);

    this.writeToFirebase = function() {
        var toWrite = this.buffer;
        this.buffer = '';

        firebaseEntry.push({
            type: 'text',
            contents: toWrite,
        });
    };
};
