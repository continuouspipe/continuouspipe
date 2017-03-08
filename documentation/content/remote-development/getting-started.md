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

If you use [Homebrew](https://brew.sh/), you can install `cp-remote` via:

```
brew install continuouspipe/tools/cp-remote
```
Otherwise you can install it manually via:

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

If your architecture is 32-bit use `windows-386.gz` rather than `windows-amd64.gz`.

**Dependencies:** You need to have `git`, and `cwRsync` installed and available in your environment `PATHS` variable.
