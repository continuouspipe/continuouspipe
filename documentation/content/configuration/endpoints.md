---
title: Endpoints
menu:
  main:
    parent: 'configuration'
    weight: 120
weight: 120
---

## Using a Standard Endpoint

The standard way to expose your services to the internet is to use the following configuration:
 
``` yaml
tasks:
    deployment:
        deploy:
            # ...
            services:
                web:
                    specification:
                        accessibility:
                            from_external: false
```

This is fine for most purposes, but has it has three main limitations: 

**1)** It presents difficulties when trying to whitelist IP addresses for authentication as the client IP address is not passed through to the service. This can be resolved by using an annotation, which ensures that the client IP address is passed through to the service: 

``` yaml
tasks:
    deployment:
        deploy:
            # ...
            services:
                web:
                    endpoints:
                      - name: www
                        annotations:
                          service.beta.kubernetes.io/external-traffic: OnlyLocal
                    specification:
                        accessibility:
                            from_external: false
```

**2)** The IP addresses are chosen from a random pool and by default are not static, which means they cannot be reliably associated with a DNS record.

**3)** It uses a public IP address per service, which can be expensive. 

## Using a Load Balancer

Most cloud services offer the option of using a load balancer, which mitigates two of the above limitations - with a load balancer public IP addresses are passed through to the service, and the IP address of the load balancer is static so can be set up in a DNS record. However, using load balancers can also be expensive as you still use a public IP address for each service.

## Using the Nginx Ingress Controller

Using the [Nginx Ingress controller](https://github.com/kubernetes/ingress/tree/master/controllers/nginx) presents an option that mitigates all three of the above limitations, as it only requires one public IP address - a series of rules can be used to determine how inbound connections are directed to your services. The Nginx Ingress controller also allows SSL to be configured in one place so removes the need to configure SSL for each service independently.

### Installation

ContinuousPipe currently provides no way to create the Nginx Ingress controller - this needs to be done as a manual step when setting up your Kubernetes cluster. Once the Ingress is deployed, it will be assigned an IP address from a random pool, which will then be static for the lifetime of the Ingress. The IP address of the manually deployed Ingress then needs to be set up in a DNS rule so that it is associated with your domain, unless you are using [Cloudflare integration](#nginx-ingress-with-cloudflare-ssl).

**Instructions for deploying the Ingress and retrieving the IP address for use in DNS are available here:** https://github.com/continuouspipe/nginx-ingress-controller.

### ContinuousPipe Modifications

The Nginx Ingress controller used by ContinuousPipe is slightly modified from the default Kubernetes behaviour. The default Nginx Ingress controller only exposes port 443 if a SSL certificate with matching hostname is also configured. The ContinuousPipe Nginx Ingress controller extends that behaviour to allow port 443 to be exposed, using a wildcard SSL certificate if no matching hostname certificate is configured. Additionally, it allows Cloudflare SSL to be configured.

### Nginx Ingress With No SSL

If you wish to use the Nginx Ingress without SSL, this can be done as follows:


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

As you can see, the setup is quite straight forward. Within `endpoints`, the Ingress is defined as `nginx`. A `host_suffix` is supplied, which will be combined with the branch name to create the full URL e.g. a deployment of "master" branch would create an environment with URL "http&#58;//master-example-with-no-ssl.yourdomain.com".

Additionally, port 80 is exposed, so traffic will just use HTTP.

{{< note title="Note" >}}
You will need to set up a wildcard DNS rule associating the Ingress IP address with your domain name for this example to work.

e.g. `123.123.123.123 *example-with-no-ssl.yourdomain.com`
{{< /note >}}

### Nginx Ingress With Own SSL

If you wish to use the Nginx Ingress with SSL, this can be done as follows: 

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
You will need to set up a DNS rule associating the Ingress IP address with your domain name for this example to work.

e.g. `123.123.123.123 example-with-ssl.yourdomain.com`
{{< /note >}}

### Nginx Ingress With Wildcard SSL

If you wish to use the Nginx Ingress with wildcard SSL, this can be done as follows:

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

This is similar to the previous configuration, but omits the `ssl_certificates`. However, as port 443 is still exposed ContinuousPipe will still set up SSL using wildcard certificates. This behaviour is distinct to the ContinuousPipe adaptation of the Nginx Ingress controller - the default Nginx Ingress controller would not set up SSL in this scenario.

{{< note title="Note" >}}
You will need to set up a wildcard DNS rule associating the Ingress IP address with your domain name for this example to work.

e.g. `123.123.123.123 *example-with-wildcard-ssl.yourdomain.com`
{{< /note >}}

### Nginx Ingress With Cloudflare SSL

ContinuousPipe already has [integration with Cloudflare]({{< relref "configuration/cloudflare-integration.md" >}}) when configuring endpoints directly. This integration can also be applied to the Nginx Ingress. The major advantage of this is that it does not need a DNS rule setting up in advance, as Cloudflare will create this automatically.

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

Further down, the Ingress defines a `host_suffix` that matches the Cloudflare `record_suffix`, so that the environment URL and the Cloudflare DNS entry are matched.
