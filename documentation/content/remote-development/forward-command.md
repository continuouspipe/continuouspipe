---
title: Forward Command
menu:
  main:
    parent: 'remote-development'
    weight: 100

weight: 100
---
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
cp-remote forward -s db 3307:3306
```

Here the local port 3307 is forward to 3306 on the remote, you could then connect using:

```
mysql -h127.0.0.1 -P3307 -u dbuser -pdbpass dbname
```
You can also forward multiple ports to your local environment:

```
cp-remote forward -s db 3306 6379
```

This will forward both ports 3306 and 6379 to the same port in the local environment.
