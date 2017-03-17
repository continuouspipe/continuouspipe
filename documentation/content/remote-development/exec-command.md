---
title: Exec Command
menu:
  main:
    parent: 'remote-development'
    weight: 60

weight: 60
---
## Using the Exec Command

To execute a command on a container without first getting a bash session use the `exec` command. The command and its arguments need to follow `--`.

```
cp-remote exec -- ls -la
```

This will run the command on the default container specified during setup but you can specify another container to run the command on. For example, if the service you want to connect to is web:

```
cp-remote exec web -- ls -la
```

### Interactive Mode

Interactive mode allows you to connect directly to an environment that is not set up as a remote environment.

If you have not previously run interactive mode with any command, you will first need to generate a [ContinuousPipe API key](https://authenticator.continuouspipe.io/account/api-keys).

You can then use interactive mode by using the `--interactive` or `-i` flags. You will also need to supply the following flags:

- `--environment` or `-e` - the environment identifier
- `--service` or `-s` - the service name
- `--flow-id` or `-f` - the flow identifier

The full command looks like this:

```
cp-remote exec --interactive --environment php-example-cpdev-foo --service web --flow-id 1268cc54-0c360641bb54 -- /bin/sh
cp-remote exec -i -e php-example-cpdev-foo -s web -f 1268cc54-0c360641bb54 -- /bin/sh
```

If you have not previously run interactive mode with any command, the first time you run this you will be asked to enter your ContinuousPipe username and the ContinuousPipe API key you generated. They are then stored in a local configuration file `.cp-remote-settings.yml` so you won't need to enter them again. 

{{< note title="Note" >}}
If you need to reset the stored credentials you need to run the [init command]({{< relref "remote-development/init-command.md#interactive-mode" >}}) with the `--reset` flag.
{{< /note >}}