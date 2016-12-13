# 2. Store the new tide projections into Firebase

Date: 13/12/2016

## Status

Accepted

## Context

We want to be able to store a projection of the tides and especially their tasks statuses. The aim is to display
a per-task detailed pipeline-like UI, as much real time as possible.

## Decision

Firebase helped us a lot with the logs and appear to be successful, it makes sense to extend its usage
to this projection.

In order to ensure a permission control, the tides will be stored with the following hierarchy:

- `/flows/{flowUuid}/tides/{tideUuid}`


## Consequences

The coupling with this 3rd party dependency is even higher. We need to sort the permission control
on this database, that is accessed directly by the clients.

We need to sort out an archiving of these detailed tides.
