# Documentation

This documentation is the technical documentation about ContinuousPipe, and its different components.

## Builder

Responsible of building the Docker images, this component is built as an independant component.

### Interfaces

Input: 
- `ContinuousPipe\Builder\Client\BuilderClient` interface.
Output: 
- `ContinuousPipe\Builder\Aggregate\Event\BuildFailed` event.
- `ContinuousPipe\Builder\Aggregate\Event\BuildFinished` event.
