var WebSocketServer = Meteor.npmRequire('ws').Server;

var ConnectionHandler = function(connection)
{
    /**
     * Start handling the connection.
     *
     */
    this.handle = function()
    {
        connection.on('message', Meteor.bindEnvironment(function(message) {
            this.receive(JSON.parse(message));
        }.bind(this)));
    };

    /**
     * Send a message through the WebSocket.
     *
     * @param message
     */
    this.send = function(message)
    {
        connection.send(JSON.stringify(message));
    };

    /**
     * Receive the following message.
     *
     * @param message
     */
    this.receive = function(message)
    {
        var entity = null;

        if (message.action == 'create') {
            entity = LogRepository.insert(message.body);
        } else if (message.action == 'update') {
            if (LogRepository.update(message.id, message.body)) {
                entity = Logs.findOne({_id: message.id});
            }
        }

        this.send({
            status: entity !== null ? 200 : 400,
            body: entity
        });
    };
};

Meteor.startup(function () {
    console.log('Starting WS server', 'isServer=', Meteor.isServer, 'isClient=', Meteor.isClient);
    var server = new WebSocketServer({port: 8080});
    server.on('connection', Meteor.bindEnvironment(function (connection) {
        new ConnectionHandler(connection).handle();
    }));
});
