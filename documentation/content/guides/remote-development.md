---
title: Remote Development
menu:
  main:
    parent: 'guides'
    weight: 90

weight: 90
---
We provide a command line tool to help with using ContinuousPipe as a remote development environment.

This helps to create, build and destroy remote environments and keep files in sync with the local filesystem.

## Prerequisites

You will need the following:

 * A ContinuousPipe hosted project with the GitHub integration set up
 * The project checked out locally
 * The IP address, username and password to use for Kubenetes cluster
 * rsync installed locally
 * A [keen.io](https://keen.io) write token, project id and event collection name if you want to log usage stats

Note: if the GitHub repository is not the origin of your checked out project then you will
need to add a remote for that repository.

## Installation

OSX (64bit):

```
sudo curl https://continuouspipe.github.io/remote-environment-client/0.0.1/darwin-amd64.gz > cp-remote.gz
gzip -d cp-remote.gz;
mv cp-remote /usr/local/bin/cp-remote
chmod +x /usr/local/bin/cp-remote
```

Dependencies: You need to have 'git', and 'rsync' installed and available in the shell where cp-remote runs

Linux (64-bits):

if your architecture is 32bit use linux-386.gz rather than linux-amd64.gz

```
sudo curl https://continuouspipe.github.io/remote-environment-client/0.0.1/linux-amd64.gz > cp-remote.gz
gzip -d cp-remote.gz;
mv cp-remote /usr/local/bin/cp-remote
chmod +x /usr/local/bin/cp-remote
```

Dependencies: You need to have 'git', and 'rsync' installed and available in the shell where cp-remote runs

Windows (64-bits):

if your architecture is 32bit use windows-386.gz rather than windows-amd64.gz

* Download https://continuouspipe.github.io/remote-environment-client/0.0.1/windows-amd64.gz
* Extract cp-remote.gz
* Move cp-remote.exe into your project folder

Dependencies: You need to have 'git', and 'cwRsync' installed and available in your environment PATHS variable

### Migrate to CP-Remote Go

- Install the latest version, see instructions in the Installation section above
- In your project directory, run `cp-remote setup` (see Setup section below)

**Changes in command arguments**

In the Go version, the commands arguments needs to be passed as flags.
To find out information about the available flags for each command run `cp-remote [command] --help` or `cp-remote [command] -h`

**Examples:**

Previously to open a bash remote shell overriding the default service,
the command using the bash script would have been `cp-remote bash web`.
This has been updated to `cp-remote bash -s web`, alternatively you may use the full flag name `cp-remote bash --service web`

To execute a command onan environment which differs from the default one.
The previous command would have been `cp-remote exec --namespace=project-key-feature-my-shiny-new-work -- ls -l`.
This has been updated to `cp-remote exec --project-key example --remote-branch feature-my-shiny-new-work -- ls -l`.
Alternatively a more concise version is `cp-remote exec -p example -r feature-my-shiny-new-work -- ls -l`.

## Setup

```
cp-remote setup
```

To start using this tool for a project, run the `setup` command from the project root.
 This will install kubectl if you do not have it installed already. It will then
 ask a series of questions to get the details for the project set up. [Please read the further
 information about these questions](#configuration) in the Configuration section below before
 running this command for the first time.

 Many of the answers are project specific, it is advisable to provide details of the answers in the
 project specific README and to securely share sensitive details, such as the cluster password with
 team members rather than them rely on the general information provided here.

Your answers will be stored in a `.cp-remote-env-settings` file in the project root. You
 will probably want to add this to your .gitignore file.

## Creating and building remote environment

```
cp-remote build
```

### Creating a new remote environment

The `build` command will push changes the branch you have checked out locally to your remote
 environment branch. ContinuousPipe will then build the environment. You can use the [ContinuousPipe admin
 site](https://ui.continuouspipe.io/) to see when the environment has finished building and
 to find its IP address.

### Rebuilding the remote environment

 To rebuild your remote environment to use the current branch you have checked out you can use the
  `build` command. This will force push the current branch which will make ContinuousPipe rebuild the
  environment. If the remote environment has the latest commit then it would not be rebuilt, in order
  to force the rebuild an empty commit is automatically made.

## Watch

 ```
 cp-remote watch
 cp-remote wa
 ```

 The `watch` command will sync changes you make locally to a container that's part of the remote environment.
 This will use the default container specified during setup but you can specify another container to sync with.
 For example, if the service you want to sync to is web:

  ```
  cp-remote watch -s web
  ```
The watch command should be left running, it will however need restarting whenever the remote environment
is rebuilt.

## Bash

 ```
 cp-remote bash
 cp-remote ba
 ```

 This will remotely connect to a bash session onto the default container specified during setup but you can specify another
 container to connect to. For example, if the service you want to connect to is web:

 ```
 cp-remote bash -s web
 ```

## Execute commands on a container

To execute a command on a container without first getting a bash session use the `exec` command. The command
and its arguments need to follow `--`.

 ```
 cp-remote exec -- ls -la
 ```

 This will run the command on the default container specified during setup but you can specify another
 container to run the command on. For example, if the service you want to connect to is web:

 ```
 cp-remote exec web -- ls -la
 ```

## Fetch

  ```
  cp-remote fetch
  cp-remote fe
  ```

When the remote environment is rebuilt it may contain changes that you do not have on the local filesystem.
  For example, for a PHP project part of building the remote environment could be installing the vendors using composer.
  Any new or updated vendors would be on the remote environment but not on the local filesystem which would cause issues,
  such as autocomplete in your IDE not working correctly. The `fetch` command will copy changes  from the remote to the local
  filesystem. This will resync with the default container specified during setup but you can specify another container.
  For example to resync with the `web` container:

  ```
    cp-remote fetch web
  ```

## Port Forwarding

 ```
 cp-remote forward -s db 3306
 ```

The `forward` command will set up port forwarding from the local environment to a container
on the remote environment that has a port exposed. This is useful for tasks such as connecting
to a database using a local client. You need to specify the container and the port number
to forward. For example, with a container named db running MySql you would run:

  ```
  cp-remote forward -s db 3306
  ```

  this runs in the foreground, so in another terminal you can use the mysql client to connect:

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

## Destroy

 ```
 cp-remote destroy
 ```

The `destroy` command will delete the remote branch used for your remote environment, ContinuousPipe will
then remove the environment.

## Usage Logging

Usage stats for the longer running commands (build and resync) can be logged to https://keen.io by providing a
 write key, project id and event collection name when running the setup command. No stats will be logged
 if these are not provided.

## Working with a different environment

The `--project-key|-p` and `--remote-branch|-r` options can be used with the `watch`, `bash`, `resync`, `checkconnection`, `exec` and `forward`
 commands to run them against a different environment than the one specified during
 setup. This is useful if you need to access a different environment such as a feature branch
 environment. For example, to open a bash session on the `web` container of the `example-feature-my-shiny-new-work`
 environment you can run:

 ```
 cp-remote bash --project-key example --remote-branch feature-my-shiny-new-work -s web
 ```

  or

 ```
 cp-remote bash -p example -r feature-my-shiny-new-work -s web
 ```

## Anybar notifications

To get a status notification for the longer running commands (watch and resync) on OSX you can
 install [AnyBar](https://github.com/tonsky/AnyBar) and provide a port number to use for
 it during the `setup` command.

## Ignoring files/directories when syncing

Files/directories can be excluded from being synced by the `watch`, `resync` and `fetch` commands. This is done by
adding the files/directories to ignore to a `.cp-remote-ignore` file in the project root. This uses the standard
rsync excludes-from format.

## Checking the connection to an environment

The `checkconnection` command can be used to check that the connection details for the Kubernetes cluster are correct
and that if they are pods can be found for the environment. It can be used with the namespace option to check
another environment:

 ```
 cp-remote checkconnection
 cp-remote ck
 ```

 or

 ```
 cp-remote checkconnection --project-key example --remote-branch feature-my-shiny-new-work
 ```

## Configuration

The `setup` command uses your answers to generate a settings file `.cp-remote-env-settings` in the
root of the project. If you need to make changes to the settings you can run the `setup` command again
or you can directly edit the settings.

Note: the kubectl cluster IP address, username and password are not stored in this file. To change these
 you can run `setup` again.

### What is your ContinuousPipe project key? (PROJECT_KEY)

This is the project name used in ContinuousPipe. It will be prefixed to all the environment
names created by ContinuousPipe. You can find this on the environments page for the tide on the
[ContinuousPipe admin site](https://ui.continuouspipe.io/). For example:

![Project Key](/images/guides/remote-development/project-key.png)

Here, this is the environment for the develop branch, so the project key is `my-project`.

### What is the name of the Git branch you are using for your remote environment? (REMOTE_BRANCH)

The name of the branch you will use for your remote environment. There may be a
project specific naming convention for this e.g. `cpdev/<your name>`

### What is your github remote name?  (REMOTE_NAME)

The name of the git remote for the GitHub project which has the ContinuousPipe integration.
In most cases you will have cloned the project repo from this so this will be `origin`.

### What is the default container for the watch, bash, fetch and resync commands? (DEFAULT_CONTAINER)

This is an optional setting, if provided this will be used by the `bash`, `watch`, `fetch` and `resync` commands as
the container you connect to, watch for file changes, fetch changes from or resync with respectively unless you provide
an alternative container to the command. It is the docker-compose  service name for the container
that you need to provide, it may be called something like `web` or `app`.

If you do not provide a default container it will need to be specified every time you use the
`bash`, `watch`, `fetch` and `resync` commands.

### If you want to use AnyBar, please provide a port number e.g 1738 ? (ANYBAR_PORT)

This is only needed if you want to get [AnyBar](https://github.com/tonsky/AnyBar) notifications.
This will provide a coloured dot in the OSX status bar which will show when syncing activity is
taking place. This provides some feedback to know that changes have been synced to the remote
development environment.

A value needs to be provided in answer to the question, even if you want to
use the default port of 1738, as the notifications are not sent unless a port number is provided.

### Keen.io settings

 * What is your keen.io write key? (KEEN_WRITE_KEY)
 * What is your keen.io project id? (KEEN_PROJECT_ID)
 * What is your keen.io event collection? (KEEN_EVENT_COLLECTION)

These are only needed if you want to log usage stats using https://keen.io/.

### Kubernetes settings

What is the IP of the cluster?
What is the cluster username?

These are asked for by the `setup` command but are not stored in the project config file. The
cluster IP address and username can be found on the cluster page for the team in the
[ContinuousPipe admin site](https://ui.continuouspipe.io/):

![Project Key](/images/guides/remote-development/kubernetes-config.png)

* What is the cluster password?

The password can be provided by your ContinuousPipe administrator.