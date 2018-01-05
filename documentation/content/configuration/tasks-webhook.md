---
title: "Tasks: Creating Webhooks"
menu:
  main:
    parent: 'configuration'
    weight: 80

weight: 80
---

You may want to configure a third party application to receive notifications about tide activity. For this you can use the `web_hook` task, which is one of the [inbuilt tasks]({{< relref "configuration/tasks.md#inbuilt-tasks" >}}). 

When the `web_hook` task is added the end of the task list, and the prior tide tasks complete successfully, a body of JSON encoded metadata will be sent to a configured URL. The metadata contains information about the tide, the git repository and branch, and the deployed endpoints. A [sample metadata output]({{< relref "#metadata-format" >}}) is shown below. 

If the prior tasks fail, the tide will fail, so the webhook will not be fired. Additionally, if the webhook does not receive a 200 HTTP status response the task will fail and the tide will fail.

In the following example a `third_party_integration` task containing a `web_hook` task has been added at the end of the task list. Assuming `images` and `deployment` are successful the webhook will fire for the address configured in the `url` property. 

``` yaml
tasks:
    images:
        # ...
    deployment:
        # ...
    third_party_integration:
        web_hook:
            url: https://example.com/my-webhook
```

In practice, you will probably not want to store the URL of the webhook in version control, so it is recommended that a variable is used instead. In the example below, the value of the `url` property would instead be stored in a `WEBHOOK_URL` variable set on the [configuration page for the flow]({{< relref "quick-start/configuring-a-flow.md" >}}) in the ContinuousPipe console.

``` yaml
tasks:
    images:
        # ...
    deployment:
        # ...
    third_party_integration:
        web_hook:
            url: ${WEBHOOK_URL}
```

## Metadata Format

A sample of the JSON encoded metadata body is as follows:

``` json
{
  "uuid":"0dbf00d2-1625-11e7-920e-0a580a840256",
  "url":"http:\/\/requestb.in\/17cuv5x1",
  "code_reference":
  {
    "code_repository":
    {
      "identifier":"84107753",
      "address":"https:\/\/api.github.com\/repos\/pswaine\/hello-world",
      "organisation":"pswaine",
      "name":"hello-world",
      "private":false,
      "default_branch":"master",
      "type":"github"
    },
    "sha1":"469fbe737ca276a9029d86b225a7bdb2ebfc8123",
    "branch":"master"
  },
  "public_endpoints":
  [{
    "name":"web",
    "address":"5ff322e0-0818-11e7-ad00-0a580a840404-master-hello-world.continuouspipe.net",
    "ports":
    [{
      "number":80,
      "protocol":"TCP"
    }]
  },
  {
    "name":"web",
    "address":"5ff322e0-0818-11e7-ad00-0a580a840404-master-hello-world.continuouspipe.net",
    "ports":
    [{
      "number":80,
      "protocol":"TCP"
    }]
  }]
}
```