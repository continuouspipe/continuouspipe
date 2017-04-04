---
title: Configuring Users
menu:
  main:
    parent: 'quick-start'
    weight: 25

weight: 25
---

When you create a project, you are automatically added as the default user:

![](/images/quick-start/project-users-overview-default-user.png)

There are two levels of user permissions:

Permission | Description                            | Restrictions
-----------|----------------------------------------|-----------------------
**ADMIN**  | User has full access permissions       | n/a
**USER**   | User has restricted access permissions | Adding and removing other users <br/> Saving flow configuration <br /> Deleting environments

The default user has ADMIN permissions. 

{{< note title="Note" >}}
If you don't need to add additional users to your project, you can skip straight to to [configuring a cluster]({{< relref "configuring-a-cluster.md" >}}).
{{< /note >}}

## Adding Users

At present ContinuousPipe uses GitHub authentication, so new users will need a GitHub account to be able to log in. If the new user already has a GitHub account they can be added directly. If not, they can be invited via email, and they will need to create a GitHub account as part of the login process.

### Add User Using GitHub Username

If the new user already has a GitHub account, you will first need them to advise you of their username. 

Then click the “ADD” button in the top right of the interface.

You will then be asked to enter the following information:

* **Username or Email** - Enter the GitHub username
* **Permissions** - Choose whether the new user should have ADMIN or USER permissions

Then click "ADD" to finish.

The owner of that account will have instant access to the project by going to the [ContinuousPipe console login screen](https://ui.continuouspipe.io/) and clicking "Login with GitHub". If the user is already authenticated with GitHub, they will be logged straight into the console. Otherwise the user will need to enter their GitHub login credentials.

### Invite User Using Email Address 

If the new user doesn't have a GitHub account, you will first need them to advise you of their email address. 

Then click the “ADD” button in the top right of the interface.

You will then be asked to enter the following information:

* **Username or Email** - Enter the email address
* **Permissions** - Choose whether the new user should have ADMIN or USER permissions

Then click "ADD" to finish. 

{{< warning title="Warning" >}}
At this point, if the email address does actually turn out to be associated with a GitHub account, the user will be added directly as described in the above section. Therefore no invitation will be sent.
{{< /warning >}}

You will be asked to confirm the invitation:

![](/images/quick-start/project-users-add-user-email-confirm-invite.png)

The owner of that email address will be sent an invitation. They will need to open the invitation email and click on "Accept invitation". This will take them to the [ContinuousPipe console login screen](https://ui.continuouspipe.io/) where they need to click "Login with GitHub". They will then need to create a new GitHub account as part of the login process, which can be done from the GitHub login screen by clicking on "Create an account".
