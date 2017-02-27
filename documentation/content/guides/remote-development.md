---
title: Remote Development
menu:
  main:
    parent: 'guides'
    weight: 30

weight: 30
---
ContinuousPipe can be used as a remote development environment using the `cp-remote` command line tool. It helps to create, build and destroy remote environments and keep files in sync with the local filesystem.

## Prerequisites

You will need the following:

 * A ContinuousPipe hosted project with the GitHub, Docker and Kubernetes integration set up
 * The project checked out locally
 * The IP address, username and password to use for the Kubenetes cluster
 * `rsync` installed locally
 * Optionally, a [keen.io](https://keen.io) write token, project id and event collection name if you want to log usage stats

{{< note title="Note" >}}
If the GitHub repository is not the origin of your checked out project then you will need to add a [Git remote](https://help.github.com/articles/adding-a-remote/) for that repository.
{{< /note >}}

## Installation

### OSX (64-bit):

```
sudo curl https://continuouspipe.github.io/remote-environment-client/0.0.1/darwin-amd64.gz > cp-remote.gz
gzip -d cp-remote.gz;
mv cp-remote /usr/local/bin/cp-remote
chmod +x /usr/local/bin/cp-remote
```

**Dependencies:** You need to have `git`, and `rsync` installed and available in the shell where `cp-remote` runs.

### Linux (64-bit):

```
sudo curl https://continuouspipe.github.io/remote-environment-client/0.0.1/linux-amd64.gz > cp-remote.gz
gzip -d cp-remote.gz;
mv cp-remote /usr/local/bin/cp-remote
chmod +x /usr/local/bin/cp-remote
```

If your architecture is 32-bit use `linux-386.gz` rather than `linux-amd64.gz`.

**Dependencies:** You need to have `git`, and `rsync` installed and available in the shell where `cp-remote` runs.

### Windows (64-bit):

* Download https://continuouspipe.github.io/remote-environment-client/0.0.1/windows-amd64.gz
* Extract `cp-remote.gz`
* Move `cp-remote.exe` into your project folder

If your architecture is 32bit use `windows-386.gz` rather than `windows-amd64.gz`.

**Dependencies:** You need to have `git`, and `cwRsync` installed and available in your environment `PATHS` variable.

## Setup

To start using this tool for a project, run the `setup` command from the project root.

```
cp-remote setup
```

It will ask a series of questions to get the details for the project set up. The [remote development cli configuration]({{< relref "faq/how-do-I-configure-the-remote-development-cli-tool.md" >}}) page gives more detailed information about the questions. 

Many of the answers are project specific, so it is advisable to provide details of the answers in your project specific README and to securely share sensitive details (such as the cluster password) with team members rather than them rely on the general information provided here.

Your answers will be stored in a `.cp-remote-env-settings.yml` file in the project root. You will probably want to add this to your `.gitignore` file.

## Using the Build Command

```
cp-remote build
```

### Creating a New Remote Environment

The `build` command will push changes the branch you have checked out locally to your remote environment branch. ContinuousPipe will then build the environment. You can use the [ContinuousPipe console](https://ui.continuouspipe.io/) to see when the environment has finished building and to find its IP address.

### Rebuilding the Remote Environment

The `build` command is also used to rebuild your remote environment. Assuming you are on the same branch used to create the new remote environment the command will force push the branch which will make ContinuousPipe rebuild the environment. If there has been no commit since the last build an empty commit is automatically made to force the rebuild.

## Using the Watch Command

```
cp-remote watch
cp-remote wa # alias
```

The `watch` command will sync changes you make locally to a container that's part of the remote environment. This will use the default container specified during setup but you can specify another container to sync with.

For example, if the service you want to sync to is web:

```
cp-remote watch -s web
```

The `watch` command should be left running, it will however need restarting whenever the remote environment is rebuilt using `build`.

## Using the Bash Command

```
cp-remote bash
cp-remote ba # alias
```

This will remotely connect to a bash session on the default container specified during setup but you can specify another container to connect to. For example, if the service you want to connect to is web:

```
cp-remote bash -s web
```

## Using the Exec Command

To execute a command on a container without first getting a bash session use the `exec` command. The command and its arguments need to follow `--`.

```
cp-remote exec -- ls -la
```

This will run the command on the default container specified during setup but you can specify another container to run the command on. For example, if the service you want to connect to is web:

```
cp-remote exec web -- ls -la
```

## Using the Fetch Command

```
cp-remote fetch
cp-remote fe # alias
```

When the remote environment is rebuilt it may contain changes that you do not have on the local filesystem.

For example, for a PHP project part of building the remote environment could be installing the vendors using composer. Any new or updated vendors would be on the remote environment but not on the local filesystem which could cause issues, such as autocomplete in your IDE not working correctly. The `fetch` command will copy changes from the remote to the local filesystem. This will resync with the default container specified during setup but you can specify another container.

For example to resync with the `web` container:

```
cp-remote fetch web
```

## Using the Forward Command

The `forward` command will set up port forwarding from the local environment to a container on the remote environment that has a port exposed. This is useful for tasks such as connecting to a database using a local client. You need to specify the container and the port number to forward. For example, with a container named `db` running MySQL you would run:

```
cp-remote forward -s db 3306
```

This runs in the foreground, so in another terminal you can use the MySQL client to connect:

```
mysql -h127.0.0.1 -u dbuser -pdbpass dbname
```

You can specify a second port number if the remote port number is different to the local port number:

```
cp-remote forward -s db 3307 3306
```

Here the local port 3307 is forward to 3306 on the remote, you could then connect using:

```
mysql -h127.0.0.1 -P3307 -u dbuser -pdbpass dbname
```

## Using the Destroy Command

```
cp-remote destroy
```

The `destroy` command will delete the remote branch used for your remote environment. ContinuousPipe will
then remove the environment.

## Using the Check Connection Command

```
cp-remote checkconnection
cp-remote ck # alias
```

The `checkconnection` command can be used to check that the connection details for the Kubernetes cluster are correct, and if they are that pods can be found for the environment. It can be used with the namespace option to check another environment:

```
cp-remote checkconnection --project-key example --remote-branch feature-my-shiny-new-work
```

## Working with a Different Environment

The `--project-key|-p` and `--remote-branch|-r` options can be used with the `watch`, `bash`, `resync`, `checkconnection`, `exec` and `forward` commands to run them against a different environment than the one specified during setup. This is useful if you need to access a different environment such as a feature branch environment. For example, to open a bash session on the `web` container of the `example-feature-my-shiny-new-work` environment you can run:

```
cp-remote bash --project-key example --remote-branch feature-my-shiny-new-work -s web
```

or

```
cp-remote bash -p example -r feature-my-shiny-new-work -s web
```

## Usage Logging

Usage stats for the longer running commands (build and resync) can be logged to https://keen.io by providing a write key, project id and event collection name when running the setup command. No stats will be logged if these are not provided.

## AnyBar Notifications

To get a status notification for the longer running commands (watch and resync) on OSX you can install [AnyBar](https://github.com/tonsky/AnyBar) and provide a port number to use for it during the `setup` command.

## Ignoring Files/Directories when Syncing

Files/directories can be excluded from being synced by the `watch`, `resync` and `fetch` commands. This is done by adding the files/directories to ignore to a `.cp-remote-ignore` file in the project root. This uses the standard rsync excludes-from format.
