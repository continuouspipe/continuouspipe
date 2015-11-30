var WebSocketServer = Meteor.npmRequire('ws').Server,
    server = new WebSocketServer({ port: 8080 });

var ConnectionHandler = function(connection)
{
    /**
     * Start handling the connection.
     *
     */
    this.handle = function()
    {
        connection.on('message', function(message) {
            this.receive(JSON.parse(message));
        }.bind(this));
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

server.on('connection', function(connection) {
    (new ConnectionHandler(connection)).handle();
});
