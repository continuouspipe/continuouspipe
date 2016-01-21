
module.exports = function() {
    var WebSocket = require('ws'),
        request = require('request');

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

    this.Given(/^I have a text log containing "([^"]*)" as child of "([^"]*)" that have the identifier "([^"]*)"$/, function (contents, parent, id, callback) {
        if (this.server.call('log.find', id).status != 404) {
            this.server.call('log.remove', id);
        }

        var inserted = this.server.call('log.insert', {
            _id: id,
            type: 'text',
            parent: parent,
            contents: contents
        });

        if (inserted._id == id) {
            callback();
        } else {
            callback(new Error('The insert log do not match the expectations'));
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

        ws.on('error', function(error) {
            callback(new Error(error));
        });
    });

    this.When(/^I send a ([A-Z]+) request to "([^"]*)" containing:$/, function (method, path, body, callback) {
        request[method.toLowerCase()]({
            headers: {'content-type' : 'application/json'},
            url: 'http://localhost:3000'+path,
            body: body
        }, function(error, response, body){
            if (error) {
                callback(error);
            }

            callback();
        });
    });

    this.Then(/^I should see "([^"]*)" under the log "([^"]*)"$/, function (message, logId, callback) {
        callback(CanISeeUnderTheLog(this.server, message, logId));
    });

    this.Then(/^I should not see "([^"]*)" under the log "([^"]*)"$/, function (message, logId, callback) {
        callback(
            CanISeeUnderTheLog(this.server, message, logId) ? undefined : new Error('I can see '+message+' under the log')
        );
    });

    var CanISeeUnderTheLog = function(server, message, logId) {
        var children = server.call('log.children', logId);
        if (children.length == 0) {
            return new Error('No children for log '+logId);
        }

        var matchingChildren = children.filter(function(log) {
            return log.contents.indexOf(message) != -1;
        });

        if (matchingChildren.length == 0) {
            return new Error('No matching children found');
        }

        return undefined;
    };
};
