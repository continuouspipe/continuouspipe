
Meteor.startup(function () {
    var tree = {
        type: 'text',
        contents: 'Tide #UUID',
        children: [
            {type: 'text', contents: 'Building application images', children: [
                {type: 'text', contents: 'Parsing application components configuration', children: [
                    {type: 'tab-container', children: [
                        {type: 'tab', contents: 'File content', children: [
                            {type: 'raw', children: [
                                {type: 'text', contents: 'd2ViOg0KICAgIGJ1aWxkOiAuDQogICAgbGlua3M6DQogICAgICAgIC0gbXlzcWwNCiAgICBleHBvc2U6DQogICAgICAgIC0gODANCiAgICBsYWJlbHM6DQogICAgICAgIGNvbS5jb250aW51b3VzcGlwZS5wdWJsaWM6IHRydWUNCiAgICAgICAgY29tLmNvbnRpbnVvdXNwaXBlLmltYWdlLW5hbWU='},
                                {type: 'text', contents: 'OiBzcm96ZS9waHAtZXhhbXBsZQ0KbXlzcWw6DQogICAgaW1hZ2U6IG15c3FsDQogICAgZW52aXJvbm1lbnQ6DQogICAgICAgIE1ZU1FMX1JPT1RfUEFTU1dPUkQ6IHJvb3QNCiAgICBleHBvc2U6DQogICAgICAgIC0gMzMwNg0K'}
                            ]}
                        ]},
                        {type: 'tab', contents: 'Components', children: [
                            {type: 'table', columns: ['Name', 'Target image'], children: [
                                {type: 'row', columns: ['app', 'sroze/php-example']}
                            ]}
                        ]}
                    ]}
                ]}
            ]}
        ]
    };

    function insertLog(log, parent) {
        var children = log.children;
        if (children) {
            delete log.children;
        }

        log.parent = parent;
        var insertedId = Logs.insert(log);

        if (children) {
            for (var i = 0; i < children.length; i++) {
                insertLog(children[i], insertedId);
            }
        }

        return insertedId;
    }

    if (Logs.find().count() == 0) {
        var logId = insertLog(tree);

        console.log('Created a log tree with ID #'+logId);
    }
});

