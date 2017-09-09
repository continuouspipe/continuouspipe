const sinon = require('sinon')
const LogWritter = require('../../src/watcher/log-writter');
const sleep = require('sleep');

describe('LogWritter: write logs to Firebase', () => {
    var firebasePushSpy;
    var writter;

    beforeEach(() => {
        firebasePushSpy = sinon.spy();

        writter = new LogWritter({
            push: firebasePushSpy
        })
    })

    it('write text inside a `text` child', () => {
        writter.write('FOO');

        sinon.assert.calledOnce(firebasePushSpy);
        sinon.assert.calledWith(firebasePushSpy, {
            type: 'text',
            contents: 'FOO'
        });
    })

    it('writes maximum every 100 ms', () => {
        clock = sinon.useFakeTimers();

        writter.write('FOO');
        writter.write('BAR');
        clock.tick(50);
        writter.write('BAZ');
        clock.tick(50);
        writter.write('AFTER');
        clock.tick(100);

        clock.restore();

        sinon.assert.match(firebasePushSpy.args[0][0], {
            type: 'text',
            contents: 'FOO'
        });

        sinon.assert.match(firebasePushSpy.args[1][0], {
            type: 'text',
            contents: 'BARBAZ'
        }); 

        sinon.assert.match(firebasePushSpy.args[2][0], {
            type: 'text',
            contents: 'AFTER'
        });        
    })
})
