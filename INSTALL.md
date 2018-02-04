# Installing ContinuousPipe

ContinuousPipe can be installed and run in multiple ways. This guide will guide you through the list of requirements
and how to get started with CP.

1. [Requirements](#requirements)
2. Running ContinuousPipe
   - [Option 1: With DockerCompose](#option-1-with-dockercompose)
   - [Option 2: With Kubernetes](#option-2-with-kubernetes)
3. [Usage](#usage)

## Requirements

To work, ContinuousPipe requires the following:

1. **GitHub OAuth Application**, to let users authenticate themselves with their GitHub account.
2. **GitHub App** (also called "GitHub Integration"). In order to connect ContinuousPipe to a set of GitHub repositories, receive
   web-hooks and 
3. **Firebase account**, to have realtime logs of your builds and deployments.

### 0. Internet Public Address

Because of the integrations with GitHub and BitBucket, your ContinuousPipe API needs to be available on
the internet, so their web-hooks can work.

**Note:** When starting ContinuousPipe with the Docker setup, an [ngrok](https://ngrok.com) tunnel is started for you
and should be displayed at the beginning when using the `start` script.

### 1. GitHub OAuth

Go to your [GitHub OAuth Apps settings](https://github.com/settings/developers) and click on **New OAuth App**. Fill in
the following details:

1. **Application name:** ContinuousPipe for *your-organisation*
2. **Homepage URL:** https://continuouspipe.io
3. **Authorization callback URL:** `$LOCAL_API_URL/auth/login/check-github` (`$LOCAL_API_URL` is the URL you use to access 
   the authentication page. On the Docker-Compose setup, it would be `http://localhost:81`)

Click the green button "Register application". After being redirected, you should be able to
read the OAuth "Client ID" and "Client Secret" to fill the `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET`
configurations.

### 2. GitHub Integration Application

Go to your [GitHub Apps settings](https://github.com/settings/apps) and click on **New GitHub app**, and fill the 
following details:

1. **Application name:** ContinuousPipe for *your-organisation*
2. **Homepage URL:** https://continuouspipe.io
3. **WebHook URL:** `$PUBLIC_API_URL/github/integration/webhook` (`$PUBLIC_API_URL` is the base URL of the public domain 
   name, such as the ngrok tunnel mentioned earlier)
4. **WebHook secret:** *a random secret string you want (keep it for later)*

From the permissions, select the following ones:
1. **Commit statuses:** Read & Write
1. **Deployments:** Read & Write
1. **Issues:** Read
1. **Pull requests:** Read & Write
1. **Repository contents:** Read & Write
1. **Organisation members:** Read

Subscribe to the following events:
1. Label
2. Repository
3. Status
4. Deployment status
5. Issues
6. Pull-requests
7. Pull-requests reviews
8. Create
9. Delete
10. Push
11. Release

Press the green button "Create GitHub App". 

You should arrive on a page summarizing your GitHub integration.
The `GITHUB_INTEGRATION_ID` configuration will be the "ID" displayed in the "About" section. 
The `GITHUB_SECRET` configuration is what you typed as a "WebHook secret" earlier. 

The `GITHUB_INTEGRATION_SLUG` is the name for the integration in the URL 
(given the URL `https://github.com/settings/apps/continuouspipe-for-samuel`, the slug would be `continuouspipe-for-samuel`).

Last but not least, you need to generate the private key for this integration by clicking on the "Generate private key" button. 
Once you have downloaded the `.pem` file, paste its path in the installation process (or manually paste it to `./runtime/keys/github.pem`).

### 3. Firebase

In order to display the real-time logs of your builds and deployments, ContinuousPipe uses Firebase Realtime Database. You 
need an account and a project for ContinuousPipe.

**Note:** the Free plan should be enough for most of the usages. ContinuousPipe can also archive logs to a Google Cloud Storage
bucket after a given amount of time.

Go to the [Firebase Console](https://console.firebase.google.com/) and create a new project. In your "Project Overview > Settings",
you can read the "Project ID" for the `FIREBASE_APP` configuration. The `FIREBASE_WEB_API_KEY` is just bellow, titled "Web API Key".

You will need a service account as well. In the "Settings", go to the "Service accounts" tab. Make sure the "Firebase Admin SDK"
tab is selected on the left and press "Generate new private key". Same than for the GitHub key, use the path of the downloaded 
file within the configurator to propagate the key (or manually paste it to `./runtime/keys/firebase.json`).

## Starting ContinuousPipe

ContinuousPipe is a web application. You can run it in various different ways but we've especially worked on two options:

1. [With DockerCompose](#option-1-docker-compose), on your own machine(s). It is started by default in development mode (which is slower than usual) 
   and is especially useful to give a try to ContinuousPipe.

2. [With `kubectl` on your Kubernetes cluster](#option-2-kubernetes). You already have a cluster and configured your `kubectl` command line tool, you can deploy
   ContinuousPipe very easily.

### Option 1: DockerCompose

1. Clone this repository
```
git clone https://github.com/continuouspipe/continuouspipe
```

2. Run the following command:
```
runtime/bin/start
```

3. Answer all the questions with the help of the [Requirements section](#requirements), and you should be ready to go!

### Option 2: Kubernetes

We've packaged a set of files for you in the `/runtime/kubernetes` directory. You should be able to use them directly
with `kubectl`:

1. Clone this repository
```
git clone https://github.com/continuouspipe/continuouspipe
```

2. Create the `continuouspipe` (you can name it differently if you want) namespace:
```
kubectl create namespace continuouspipe
```

3. Create the load-balancer that is going to be used for the API
```
kubectl --namespace=continuouspipe create -f runtime/kubernetes/dist/00-api-service.yaml
```

Wait until the service has a load-balancer IP (check with `kubectl --namespace=continuouspipe get services`). Use this
address as the `RIVER_API_URL` configuration (example: `http://1.2.3.4`) for the following step.

2. Generate the configuration according to your needs and the [Requirements section](#requirements)
```
runtime/bin/generate-kube-configuration
```

4. Create the resources with the following command:
```
kubectl --namespace=continuouspipe create -f runtime/kubernetes/generated
```

5. Run the database migrations. It's the only step that requires manual intervention. Get the `api` pod name and 
```
$ kubectl --namespace=continuouspipe get pods | grep api
api-4019277730-bn6t0           1/1       Running   0          13m

$ kubectl --namespace=continuouspipe exec -it api-4019277730-bn6t0 -- sh -c 'bin/console -e=prod doctrine:migrations:migrate --no-interaction'
[...]
```

5. Get the address of your `ui` load-balancer
```
$ kubectl --namespace=continuouspipe get services/ui
NAME      TYPE           CLUSTER-IP     EXTERNAL-IP     PORT(S)        AGE
ui        LoadBalancer   10.3.247.115   35.195.150.34   80:32341/TCP   21m
```

6. Open the link! In this example `http://35.195.150.34`

## Usage

For your first time after the installation, we've got a [first usage of ContinuousPipe](FIRST_USAGE.md) guide for you.
It should help you understand what's around you and do your first deployment!

Last but not least, checkout [the documentation](https://docs.continuouspipe.io) and especially the basics and 
[ContinuousPipe's concepts](https://docs.continuouspipe.io/basics/concepts-continuous-pipe-concepts/)
