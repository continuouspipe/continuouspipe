const sinon = require('sinon')
const Archiver = require('./archiver')

describe('LogWritter: write logs to Firebase', () => {
    var firebaseReadSpy, firebaseWriteSpy, bucketReadSpy, bucketWriteSpy;
    var archiver;

    beforeEach(() => {
        firebaseReadSpy = sinon.spy();
        firebaseWriteSpy = sinon.spy();
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
})
