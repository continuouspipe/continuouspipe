---
title: Getting Started
menu:
  main:
    parent: 'remote-development'
    weight: 10

weight: 10
linkTitle: Getting Started
---
ContinuousPipe can be used as a remote development environment using the `cp-remote` command line tool. It helps to create, build and destroy remote environments and keep files in sync with the local filesystem.

## Prerequisites

You will need the following:

 * A ContinuousPipe hosted project with the GitHub or Bitbucket, Docker and Kubernetes integration set up
 * The project checked out locally
 * `rsync` installed locally
 * Optionally, a [keen.io](https://keen.io) write token, project id and event collection name if you want to log usage stats

{{< note title="Note" >}}
If the GitHub or Bitbucket repository is not the origin of your checked out project then you will need to add a [Git remote](https://help.github.com/articles/adding-a-remote/) for that repository.
{{< /note >}}

## Installation

### OSX (64-bit):

If you use [Homebrew](https://brew.sh/), you can install `cp-remote` via:

```
brew install continuouspipe/tools/cp-remote
```
Otherwise you can install it manually via:

```
sudo curl https://inviqa-cp-remote-client-environment.s3-eu-west-1.amazonaws.com/latest/darwin-amd64/cp-remote.tar.gz > cp-remote.tar.gz
tar -xzvf cp-remote.tar.gz;
mv cp-remote /usr/local/bin/cp-remote
```

**Dependencies:** You need to have `git`, and `rsync` installed and available in the shell where `cp-remote` runs.

### Linux (64-bit):

```
sudo curl https://inviqa-cp-remote-client-environment.s3-eu-west-1.amazonaws.com/latest/linux-amd64/cp-remote.tar.gz > cp-remote.tar.gz
tar -xzvf cp-remote.tar.gz;
mv cp-remote /usr/local/bin/cp-remote
```

### Linux (32-bit):

```
sudo curl https://inviqa-cp-remote-client-environment.s3-eu-west-1.amazonaws.com/latest/linux-386/cp-remote.tar.gz > cp-remote.tar.gz
tar -xzvf cp-remote.tar.gz;
mv cp-remote /usr/local/bin/cp-remote
```

**Dependencies:** You need to have `git`, and `rsync` installed and available in the shell where `cp-remote` runs.

### Windows (64-bit):

* Download https://inviqa-cp-remote-client-environment.s3-eu-west-1.amazonaws.com/latest/windows-amd64/cp-remote.zip
* Extract `cp-remote.zip`
* Move `cp-remote.exe` into your project folder

### Windows (32-bit):
* Download https://inviqa-cp-remote-client-environment.s3-eu-west-1.amazonaws.com/latest/windows-386/cp-remote.zip
* Extract `cp-remote.zip`
* Move `cp-remote.exe` into your project folder

**Dependencies:** You need to have `git`, and `cwRsync` installed and available in your environment `PATHS` variable.

## Quick Start

The quick start guide gives an overview of how to get running with remote development:

- [Remote Development: Configuring Your Repository]({{< relref "quick-start/remote-development-configuring-your-repository.md" >}})
- [Remote Development: Creating a Remote Environment]({{< relref "quick-start/remote-development-creating-a-remote-environment.md" >}})
- [Remote Development: Using a Remote Environment]({{< relref "quick-start/remote-development-using-a-remote-environment.md" >}})

## Data Sharing

**ContinuousPipe receives usage and diagnostic information for each cp-remote command executed. This allows errors to be detected and fixed as soon as possible.**

Summary of information received:

- The `cp-remote` version number
- The operating system and system architecture (Linux, Windows or Mac)
- The command name including arguments (excluding the init token)
- The duration of the command
- The success/failure code of the command
- Some configuration settings (username, flow id, cluster id, environment id, remote branch name, service name, Kubernetes cluster user and address)
- Any file names configured to be ignored (if present)
- Any error stack (if present)