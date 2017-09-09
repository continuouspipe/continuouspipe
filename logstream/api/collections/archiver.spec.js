const sinon = require('sinon')
const Archiver = require('./archiver')

describe('LogWritter: write logs to Firebase', () => {
    var firebaseReadSpy, firebaseWriteSpy, bucketReadSpy, bucketWriteSpy;
    var archiver;

    beforeEach(() => {
        firebaseReadSpy = sinon.spy();
        firebaseWriteSpy = sinon.spy(function(identifier, value) {
            return Promise.resolve(value);
        });
        bucketReadSpy = sinon.spy();
        bucketWriteSpy = sinon.spy(function() {
            return Promise.resolve('file-url');
        });
    })

    it('writes the content to a google cloud storage file', () => {
        var archiver = new Archiver(
            {read: function() {
                return Promise.resolve({
                    type: 'text',
                    contents: 'FOO'
                })
            }, write: firebaseWriteSpy},
            {read: bucketReadSpy, write: bucketWriteSpy}
        );

        return archiver.archive('identifier').then(function() {
            sinon.assert.calledOnce(bucketWriteSpy);
            sinon.assert.calledWith(bucketWriteSpy, 'f393f3f5e496869a15bc72cbfd56f541.json', JSON.stringify({
                type: 'text',
                contents: 'FOO'
            }));

            sinon.assert.calledOnce(firebaseWriteSpy);
            sinon.assert.calledWith(firebaseWriteSpy, 'identifier', {
                _id: 'identifier',
                archived: true,
                archive: 'file-url'
            });
        });
    })

    it('recursively archive the referenced logs', () => {
        var archiver = new Archiver(
            {read: function(identifier) {
                if (identifier == '/logs/the-log') {
                    return Promise.resolve({
                        type: 'container',
                        children: {
                            'one': {
                                type: 'raw',
                                path: {
                                    identifier: '/raws/first/children',
                                }
                            },
                            'two': {
                                type: 'raw',
                                path: '/raws/second/children'
                            }
                        }
                    })
                }

                if (identifier == '/raws/first/children') {
                    return Promise.resolve({
                        type: 'raw',
                        logId: '/logs/the-log/one'
                    });
                }

                if (identifier == '/raws/second/children') {
                    return Promise.resolve({
                        type: 'raw',
                        logId: '/logs/the-log/two'
                    });
                }
            }, write: firebaseWriteSpy},
            {read: bucketReadSpy, write: bucketWriteSpy}
        );

        return archiver.archive('/logs/the-log').then(function() {
            sinon.assert.calledWith(firebaseWriteSpy, '/logs/the-log', {
                _id: '/logs/the-log',
                archived: true,
                archive: 'file-url'
            });

            sinon.assert.calledWith(firebaseWriteSpy, '/raws/first/children', {
                _id: '/raws/first/children',
                archived: true,
                archive: 'file-url'
            });

            sinon.assert.calledWith(firebaseWriteSpy, '/raws/second/children', {
                _id: '/raws/second/children',
                archived: true,
                archive: 'file-url'
            });

            sinon.assert.calledWith(bucketWriteSpy, sinon.match.any, JSON.stringify({
                type: 'container',
                children: { 
                    one: { 
                        _id: '/raws/first/children',
                        archived: true,
                        archive: 'file-url' 
                    },
                    two: { 
                        _id: '/raws/second/children',
                        archived: true,
                        archive: 'file-url'
                    }
                }
            }));
        });
    })
})
