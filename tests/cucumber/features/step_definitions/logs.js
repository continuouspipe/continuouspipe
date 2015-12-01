
module.exports = function() {
    var WebSocket = require('ws');

    this.Given(/^I have an empty log "([^"]*)"$/, function (logId, callback) {
        var search = this.server.call('log.find', logId);
        if (search.status == 200) {
            this.server.call('log.remove', logId);
        }

        var inserted = this.server.call('log.insert', {
            _id: logId,
            type: 'container',
            contents: ''
        });

        if (inserted._id == logId) {
            callback();
        } else {
            callback(new Error('Got a different object', inserted));
        }
    });

    this.When(/^I send the following message through the WebSocket:$/, function (string, callback) {
        var ws = new WebSocket('ws://localhost:8080');

        ws.on('open', function() {
            ws.send(string);
        });

        ws.on('message', function(body) {
            var message = JSON.parse(body);

            if (message.status == 200) {
                callback();
            } else {
                callback(new Error('Error message received: '+message.status));
            }
        });
    });

    this.Then(/^I should see "([^"]*)" under the log "([^"]*)"$/, function (message, logId, callback) {
        var children = this.server.call('log.children', logId);
        if (children.length == 0) {
            callback(new Error('No children for log '+logId));
        }

        var matchingChildren = children.filter(function(log) {
            return log.contents.indexOf(message) != -1;
        });

        if (matchingChildren.length == 0) {
            callback(new Error('No matching children found'));
        } else {
            callback();
        }
    });
};
