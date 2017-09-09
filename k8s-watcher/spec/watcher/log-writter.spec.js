const sinon = require('sinon')
const LogWritter = require('../../src/watcher/log-writter');

describe('LogWritter: write logs to Firebase', () => {
    var firebaseEntry, firebaseEntryMock;
    var writter;

    beforeEach(() => {
        firebaseEntry = {
            push: function(entry) {}
        };

        firebaseEntryMock = sinon.mock(firebaseEntry)

        writter = new LogWritter(firebaseEntry)
    })

    it('write text inside a `text` child', () => {
        firebaseEntryMock.expects('push').once().withArgs({
            type: 'text',
            contents: 'FOO'
        })

        writter.write('FOO');
    })
})
