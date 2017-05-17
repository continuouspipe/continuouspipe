---
title: "Console: Where can I get a ContinuousPipe API key?"
menu:
  main:
    parent: 'faq'
    weight: 8
weight: 8
linkTitle: ContinuousPipe API Keys
---

## ContinuousPipe API Keys

ContinuousPipe API keys can be used with the `cp-remote` tool in [interactive mode]({{< relref "remote-development/working-with-different-environments.md#interactive-mode" >}}) to access a bash terminal on a deployed container.

## Creating a ContinuousPipe API Key

You can generate a new API key in the account section of the console: https://authenticator.continuouspipe.io/account/api-keys.

To create a new API key, enter a description in the "New key" form:

{{< figure src="/images/faq/account-api-key-new.png" class="three-quarter-width" >}}

The new key will then be visible in list of keys:

{{< figure src="/images/faq/account-api-key-view.png" class="three-quarter-width" >}}

{{< note title="Note" >}} 
If you have already built a remote development environment using `cp-remote init <token>` an API key will already have been generated for you and stored in the global configuration file `~/.cp-remote/config.yml`.
{{< /note >}}