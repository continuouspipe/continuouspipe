---
title: Ingresses
menu:
  main:
    parent: 'configuration'
    weight: 120
weight: 120
---

A Kubernetes [ingress](https://kubernetes.io/docs/concepts/services-networking/ingress/#what-is-ingress) is a network component that sits between the internet and your Kubernetes [services](https://kubernetes.io/docs/concepts/services-networking/service/). It comprises a series of rules that determine how inbound connections are directed to your services, how SSL is configured, and how traffic is load balanced. Adding an ingress removes the need to configure each service independently.

Currently Kubernetes provides two ingress controllers:

- [GCE](https://github.com/kubernetes/ingress/tree/master/controllers/gce) - a load balancer ingress controller
- [Nginx](https://github.com/kubernetes/ingress/tree/master/controllers/nginx) - a web server ingress controller

## Installation

ContinuousPipe currently provides no way to create an ingress controller - this needs to be done as a manual step when setting up your Kubernetes cluster. Once the ingress is deployed, it will be assigned an IP address from a random pool, which will then be static for the lifetime of the ingress.

In order to use the Nginx ingress, the IP address of the manually deployed ingress then needs to be set up in a DNS rule so that it is associated with your domain. This will need to be a wildcard DNS rule to support the ContinuousPipe host suffix functionality, which creates a domain name dynamically by concatenating a host suffix with the deployed branch name.

## Nginx Ingress Controller

The Nginx ingress controller used by ContinuousPipe is slightly modified from the default behaviour. The default Nginx ingress controller only exposes port 443 if a SSL certificate with matching hostname is also configured. The ContinuousPipe Nginx ingress controller extends that behaviour to allow port 443 to be exposed, using a wildcard SSL certificate if no matching hostname certificate is configured. Additionally, it allows Cloudflare SSL to be configured.

### Nginx Ingress With No SSL

If you wish to use the Nginx ingress without SSL, this can be done as follows:


``` yaml
tasks:
    # ...
    deployment:
        deploy:
            cluster: ${CLUSTER}
            services:
                web:
                    endpoints:
                        - name: webnossl
                          ingress:
                              class: nginx
                              host_suffix: '-example-with-no-ssl.yourdomain.com'
                    specification:
                        ports:
                          - 80
```

As you can see, the setup is quite straight forward. Within `endpoints`, the ingress is defined as `nginx`. A `host_suffix` is supplied, which will be combined with the branch name to create the full URL e.g. a deployment of "master" branch would create an environment with URL "http&#58;//master-example-with-no-ssl.yourdomain.com".

Additionally, port 80 is exposed, so traffic will just use HTTP.

{{< note title="Note" >}}
You will need to set up a DNS rule associating the ingress IP address with your domain name for this example to work.
{{< /note >}}

### Nginx Ingress With Own SSL

If you wish to use the Nginx ingress with SSL, this can be done as follows: 

``` yaml
tasks:
    # ...
    deployment:
        deploy:
            cluster: ${CLUSTER}
            services:
                web:
                    endpoints:
                        - name: webwithssl
                          ingress:
                              class: nginx
                              host:
                                  expression: '"example-with-ssl.yourdomain.com"'
                          ssl_certificates:
                            - name: webwithssl
                              key: ${SSL_KEY}
                              cert: ${SSL_CERT}
                    specification:
                        ports:
                          - 80
                          - 443
```

This is similar to the previous configuration, but with important differences. Instead of using a `host_suffix`, a `host` expression is defined instead. This will create an environment using exactly "https://example-with-ssl.yourdomain.com".

The `ssl_certificates` section is used to define the certificate, which can be either an officially supplied certificate or a self signed certificate. As this isn't something you want to store in version control, the `key` and `cert` properties instead reference variables `${SSL_KEY}` and `${SSL_CERT}` respectively. The values need to be entered into the ContinuousPipe console as explained when [configuring a flow]({{< relref "quick-start/configuring-a-flow.md" >}}). The values entered should be base64 encoded.

In addition to port 80, port 443 is also exposed. This is needed to let ContinuousPipe know that you want to use HTTPS traffic.

{{< note title="Note" >}}
You will need to set up a DNS rule associating the ingress IP address with your domain name for this example to work.
{{< /note >}}

### Nginx Ingress With Wildcard SSL

If you wish to use the Nginx ingress with wildcard SSL, this can be done as follows:

``` yaml
tasks:
    # ...
    deployment:
        deploy:
            cluster: ${CLUSTER}
            services:
                web:
                    endpoints:
                        - name: webwithwildcardssl
                          ingress:
                              class: nginx
                              host:
                                  expression: '"example-with-wildcard-ssl.yourdomain.com"'
                    specification:
                        ports:
                          - 80
                          - 443
```

This is similar to the previous configuration, but omits the `ssl_certificates`. However, as port 443 is still exposed ContinuousPipe will still set up SSL using wildcard certificates. This behaviour is distinct to the ContinuousPipe adaptation of the Nginx ingress controller - the default Nginx ingress controller would not set up SSL in this scenario.

{{< note title="Note" >}}
You will need to set up a DNS rule associating the ingress IP address with your domain name for this example to work.
{{< /note >}}

### Nginx Ingress With Cloudflare SSL

ContinuousPipe already has [integration with Cloudflare]({{< relref "configuration/cloudflare-integration.md" >}}) when configuring endpoints directly. This integration can also be applied to the Nginx ingress. The major advantage of this is that it does not need a DNS rule setting up in advance, as Cloudflare will create this automatically.

``` yaml
tasks:
    # ...
    deployment:
        deploy:
            cluster: ${CLUSTER}
            services:
                web:
                    endpoints:
                        - name: webwithcloudflaressl
                          cloud_flare_zone:
                              zone_identifier: ${CLOUD_FLARE_ZONE}
                              authentication:
                                  email: ${CLOUD_FLARE_EMAIL}
                                  api_key: ${CLOUD_FLARE_API_KEY}
                              proxied: true
                              record_suffix: '-example-with-cloudflare-ssl.continuouspipe.net'
                          ingress:
                              class: nginx
                              host_suffix: '-example-with-cloudflare-ssl.continuouspipe.net'
                    specification:
                        ports:
                          - 80
                          - 443
```

As you can see, the main difference is the addition of a `cloud_flare_zone` section, which defines how traffic should interact with Cloudflare. The values defined in `cloud_flare_zone` are explained in the [integration with Cloudflare]({{< relref "configuration/cloudflare-integration.md" >}}) page.

Further down, the ingress defines a `host_suffix` that matches the Cloudflare `record_suffix`, so that the environment URL and the Cloudflare DNS entry are matched.
