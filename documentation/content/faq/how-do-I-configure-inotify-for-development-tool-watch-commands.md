---
title: "Remote Development Troubleshooting: How do I configure inotify for development tool watch commands?"
menu:
  main:
    parent: 'faq'
    weight: 110
weight: 110
linkTitle: Watch Commands and Inotify
---

Many development tools incorporate a watch or listen command to monitor a filesystem. 

If you are using a watch command on a remote environment you may find that it becomes unresponsive. One explanation for this is that the [inotify](https://en.wikipedia.org/wiki/Inotify) default setting for `max_user_watches` is too low to keep track of all the files in your project.

If you are using one one of the [ContinuousPipe images]({{< relref "faq/what-are-the-continuous-pipe-images.md" >}}) the default value for `max_user_watches` is 8192. Alternatively you can find out the value yourself using this command:

```text
> cp-remote exec [flags] -- cat /proc/sys/fs/inotify/max_user_watches
8192
```

To fix this issue, add the following line to your `Dockerfile`:


```docker
RUN sysctl -w fs.inotify.max_user_watches=1048576
```

You will then need to commit the `Dockerfile` change to Git and trigger a rebuild of your remote environment for it to take effect:

```text
> cp-remote build
```