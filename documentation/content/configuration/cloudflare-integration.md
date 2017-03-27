---
title: Cloudflare Integration?
menu:
  main:
    parent: 'configuration'
    weight: 110
weight: 110
linkTitle: Cloudflare Integration
---

You can integrate your endpoints with the [Cloudflare](https://www.cloudflare.com/) service. At a minimum this allows you to take advantage of Cloudflare DNS functionality and view deployed tides using a domain name (rather than just an IP address). If you choose to proxy the traffic as well, you can use advanced features in Cloudflare, including adding SSL to the endpoint.

An example of the Cloudflare endpoint integration is as follows:

```yaml
tasks:
    # ...
    deployment:
        deploy:
            services:
                web:
                    specification:
                        # ...
                        endpoints:
                            - name: web
                              cloud_flare_zone:
                                  zone_identifier: ${CLOUD_FLARE_ZONE}
                                  authentication:
                                      email: ${CLOUD_FLARE_EMAIL}
                                      api_key: ${CLOUD_FLARE_API_KEY}
                                  proxied: true
                                  record_suffix: -test.yourdomain.com
```

This configuration adds a `cloud_flare_zone` node to the `web` endpoint. The authentication credentials under `cloud_flare_zone` can be obtained from the Cloudflare console:

- `zone_identifier` is located on the overview screen of the Cloudflare console
- `email` is the email used to log into the Cloudflare console
- `api_key` is located in the account section of the Cloudflare console

{{< note title="Note" >}}
You will notice that the actual values of these credentials are not entered into the configuration - instead they are represented as the YAML variables `${CLOUD_FLARE_ZONE}`, `${CLOUD_FLARE_EMAIL}` and `${CLOUD_FLARE_API_KEY}` respectively. The values instead need to be entered into the ContinuousPipe console as explained when [configuring a flow]({{< relref "quick-start/configuring-a-flow.md" >}}).

Additionally, `${CLOUD_FLARE_ZONE}` and `${CLOUD_FLARE_API_KEY}` need to be encrypted. This can be done by clicking the padlock icon when entering the values into the flow configuration, before saving. 
{{< /note >}}

The `proxied` property determines whether traffic is proxied through Cloudflare. If set to "false" (or not set at all) then the endpoint just uses Cloudflare DNS functionality to associate a domain name with a tide (rather than just an IP address). If set to "true" then traffic passes through Cloudflare servers so is subject to any configuration set up there. The primary advantage of this is that it allows SSL to be set up on the endpoint, however it also allows many other Cloudflare features to be used by setting up [page rules](https://support.cloudflare.com/hc/en-us/articles/218411427-Page-Rules-Tutorial).

The `record_suffix` property is used to configure the domain name that will be used to view the deployed tide. It is combined with the environment identifier to form a complete url in the format `http://<environment_id><record_suffix>`. For example, with the configuration above, launching a tide with an environment identifier of "5ff322e0-0818-11e7-ad00-0a580a840404-master" would result in an endpoint URL of "http://5ff322e0-0818-11e7-ad00-0a580a840404-master-test.yourdomain.com/".

{{< warning title="Warning" >}}
The domain used in the `record_suffix` needs to match the domain associated with the Cloudflare account.
{{< /warning >}}

As you can see, using the default environment identifier results in a rather long endpoint URL. This can be improved by overriding the environment name as follows:

```yaml
tasks:
    # ...
    deployment:
        deploy:
            environment:
                name: '"hello-world-" ~ code_reference.branch'
            services:
                web:
                    specification:
                        # ...
                        endpoints:
                            - name: web
                              cloud_flare_zone:
                                  zone_identifier: ${CLOUD_FLARE_ZONE}
                                  authentication:
                                      email: ${CLOUD_FLARE_EMAIL}
                                      api_key: ${CLOUD_FLARE_API_KEY}
                                  proxied: true
                                  record_suffix: -test.yourdomain.com
```

Now when a tide is deployed, the endpoint URL will be "http://hello-world-master-test.yourdomain.com/". 

{{< note title="Note" >}}
Another way to set the environment name is by using [default configuration]({{< relref "faq/how-do-I-use-defaults-to-reduce-the-duplication-of-configuration.md" >}}).
{{< /note >}}