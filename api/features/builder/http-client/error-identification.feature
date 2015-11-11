Feature:
  In order to take action based on the errors that happen during Docker actions
  The system should be able to identify the errors based on error messages

  Scenario: Registry is not responding properly
    When the Docker daemon returns the error "Head https://registry-1.docker.io/v2/inviqasession/graze-mysql/blobs/sha256:4280dc1f38b4454b2c13ecc2164e020f3e57f0bb6f2611ed2b9c2bef16d60cef: EOF"
    Then the identified error should be a daemon network error

  Scenario: Infrastructure network problem
    When the Docker daemon returns the error "use of closed network connection"
    Then the identified error should be a daemon network error

  Scenario: Push already in progress
    When the Docker daemon returns the error "push docker.io/inviqasession/graze is already in progress"
    Then the identified error should be a push already in progress error

  Scenario: Daemon network connection issues
    When the Docker daemon returns the error "Failed to upload metadata: Put https://cdn-registry-1.docker.io/v1/images/81f2929c91fd50473aef0f72dcc507206c25b2d4673c155e7e14e00d8dc59245/json: net/http: TLS handshake timeout"
    Then the identified error should be a daemon network error
