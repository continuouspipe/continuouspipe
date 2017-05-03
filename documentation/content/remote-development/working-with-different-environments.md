---
title: Working with a Different Environment
menu:
  main:
    parent: 'remote-development'
    weight: 11

weight: 11
---

Sometimes you may want to use `cp-remote` to access an environment that you did not build using a token. Examples of this might be:

* You want to access a feature branch environment
* You want to access an environment created by a pull request
* You want to access an environment built by another developer using their own token

## When You Have An Existing Environment Built Using a Token {#token-mode}

If you have already set up a remote development environment using a token, you can use the `--kube-environment-name|-e` option to run commands against a different environment than the one specified during setup. 

The commands you can use with a token setup and the `--kube-environment-name` option are:

* [bash]({{< relref "remote-development/command-bash.md" >}}) command
* [checkconnection]({{< relref "remote-development/command-check-connection.md" >}}) command
* [exec]({{< relref "remote-development/command-exec.md" >}}) command
* [forward]({{< relref "remote-development/command-forward.md" >}}) command
* [fetch]({{< relref "remote-development/command-fetch.md" >}}) command
* [sync]({{< relref "remote-development/command-sync.md" >}}) command
* [watch]({{< relref "remote-development/command-watch.md" >}}) command

For example, to open a bash session on the `web` container of the `php-example-cpdev-foo` environment you can run:

```
cp-remote bash --environment php-example-cpdev-foo --service web
```

or

```
cp-remote bash -e php-example-cpdev-foo -s web
```

## When You Have No Existing Environment (Using Interactive Mode) {#interactive-mode}

If you have no environment, you can use the `--interactive|-i` option to run a limited set of commands against an environment.

The commands you can use with the `--interactive` option are:

* [init]({{< relref "remote-development/command-init.md#interactive-mode" >}}) command
* [bash]({{< relref "remote-development/command-bash.md#interactive-mode" >}}) command
* [exec]({{< relref "remote-development/command-exec.md#interactive-mode" >}}) command

If you have not previously run interactive mode with any command, you will first need to generate a [ContinuousPipe API key](https://authenticator.continuouspipe.io/account/api-keys).

The first time you run the the `--interactive` option with any of the above commands you will be prompted to enter your ContinuousPipe username and the ContinuousPipe API key you generated. These credentials are then stored in a global configuration file `~/.cp-remote/config.yml` (on Linux/OSX) `C:\Users\{YourUserName}\.cp-remote\config.yml` (on Windows), so you won't need to enter them again.

For the `bash` and `exec` commands you will also need to supply the following flags:

- `--kube-environment-name` or `-e` - the environment identifier
- `--service` or `-s` - the service name
- `--flow-id` or `-f` - the flow identifier

For example, to open a bash session on the `web` container of the `php-example-cpdev-foo` environment you can run:

```
cp-remote bash --interactive --kube-environment-name php-example-cpdev-foo --service web --flow-id 1268cc54-0c360641bb54
```

or

```
cp-remote bash -i -e php-example-cpdev-foo -s web -f 1268cc54-0c360641bb54
```

{{< note title="Note" >}} 
The [bash command]({{< relref "remote-development/command-bash.md#interactive-mode" >}}) and [exec command]({{< relref "remote-development/command-exec.md#interactive-mode" >}}) can be run in interactive mode directly without having to run `cp-remote init -i` first.
{{< /note >}}

{{< note title="Note" >}}
If you need to reset the stored username and API key, you need to run the [init command]({{< relref "remote-development/command-init.md#interactive-mode" >}}) with the `--reset` flag.
{{< /note >}}

