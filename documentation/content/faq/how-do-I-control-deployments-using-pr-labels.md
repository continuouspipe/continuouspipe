---
title: How do I control deployments using pull request labels?
menu:
  main:
    parent: 'faq'
    weight: 20
weight: 20
---
You may not want to deploy an environment every time a commit is pushed to a branch. One way of limiting this is to only deploy environments for branches with open pull requests with a particular label. You can do this by putting a condition on tasks in `continuous-pipe.yml using a filter expression.

```
tasks:
  images:
      build:
          # ...
          filter:
              expression: '"Ready for review" in pull_request.labels'
  deployment:
      deploy:
          # ...
          filter:
              expression: '"Ready for review" in pull_request.labels'
```

Now the images and deployment tasks will only run when a branch has a pull request open which has a "Ready for review" label. 

You may want to exclude some branches from this restriction though, for example you may have production and uat branches that you want to deploy whenever there is a change. This can be done with:

```
tasks:
  images:
      build:
          # ...
          filter:
              expression: 'code_reference.branch in ["uat", "production"] or "Ready for review" in pull_request.labels'
  deployment:
      deploy:
          # ...
          filter:
              expression: 'code_reference.branch in ["uat", "production"] or "Ready for review" in pull_request.labels'
```