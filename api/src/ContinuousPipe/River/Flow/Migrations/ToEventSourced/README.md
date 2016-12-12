# To-Event-Sourced migration

This migration is encouraged by the fact that we need to be able to ensure
consistency in the Flows and remove these `FlowContext` and `TideContext` objects
that are a simple bag, against any logical software engineering pattern.

## Migration

1. Generate the events
2. Use the event-based repository
