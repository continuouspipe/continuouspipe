Feature:
  In order to take action based on the errors that happen during Docker actions
  The system should be able to identify the errors based on error messages

  Scenario: Registry is not responding properly
    When the Docker daemon returns the error "Head https://registry-1.docker.io/v2/inviqasession/graze-mysql/blobs/sha256:4280dc1f38b4454b2c13ecc2164e020f3e57f0bb6f2611ed2b9c2bef16d60cef: EOF"
    Then the identified error should be a daemon network error

  Scenario: Registry is not responding properly again
    When the Docker daemon returns the error "Head https://registry-1.docker.io/v2/inviqasession/graze/blobs/sha256:a3ed95caeb02ffe68cdd9fd84406680ae93d633cb16422d00e8a7c22955b46d4: dial tcp 54.152.156.80:443: i/o timeout"
    Then the identified error should be a daemon network error

  Scenario: Infrastructure network problem
    When the Docker daemon returns the error "use of closed network connection"
    Then the identified error should be a daemon network error

  Scenario: Push already in progress
    When the Docker daemon returns the error "push docker.io/inviqasession/graze is already in progress"
    Then the identified error should be a push already in progress error

  Scenario: Push or pull already in progress
    When the Docker daemon returns the error "push or pull docker.io/inviqasession/ft is already in progress"
    Then the identified error should be a push already in progress error

  Scenario: Push or pull already in progress
    When the Docker daemon returns the error "push or pull docker.io/inviqasession/total-positional-reports-app is already in progress"
    Then the identified error should be a push already in progress error

  Scenario: Daemon network connection issues
    When the Docker daemon returns the error "Failed to upload metadata: Put https://cdn-registry-1.docker.io/v1/images/81f2929c91fd50473aef0f72dcc507206c25b2d4673c155e7e14e00d8dc59245/json: net/http: TLS handshake timeout"
    Then the identified error should be a daemon network error

  Scenario: Daemon network connection issues
    When the Docker daemon returns the error "Head https://dseasb33srnrn.cloudfront.net/registry-v2/docker/registry/v2/blobs/sha256/5c/5c1d75783f7f66ae3006b86d2a3868a482699942c9e4b8951c3c3fc282ec5490/data?Expires=1462800791&Signature=LK72D8I7GtxTxD3HsZ5tOiCdGYCq5aypMaDqBLAFmgtP94UOQcQN5D9TBTAFepffJ4gAjEu3Kd-VqkavCbic274gg95D9irsbYgCzqEdmXvhNhPh6F3OP8fJ-WrQ0uCFQ~GKHSDbjcckUM0W68UsBmYSakQlPDQA--cN8GBOq3U_&Key-Pair-Id=APKAJECH5M7VWIS5YZ6Q: net/http: TLS handshake timeout"
    Then the identified error should be a daemon network error

  Scenario: Daemon internal error
    When the Docker daemon returns the error "Received unexpected HTTP status: 500 Internal Server Error"
    Then the identified error should be a daemon error

  Scenario: Daemon internal error
    When the Docker daemon returns the error "Received unexpected HTTP status: 500 INTERNAL SERVER ERROR"
    Then the identified error should be a daemon error

  Scenario: Push HEAD timeout
    When the Docker daemon returns the error "Head https://quay.io/v2/sroze/ft/blobs/sha256:c4d7cdda3413170869ccb4c6803b666efd97cd83f609813291df37cd93a153f1: Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: net/http: request canceled (Client.Timeout exceeded while awaiting headers)"
    Then the identified error should be a daemon network error

  Scenario: Push PUT timeout
    When the Docker daemon returns the error "Put https://quay.io/v2/sroze/ft/manifests/51c2b6f8de95a36a49813a1bbdabd2d2a7d07e9f: Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: net/http: request canceled (Client.Timeout exceeded while awaiting headers)"
    Then the identified error should be a daemon network error

  Scenario: Push TCP error
    When the Docker daemon returns the error "Put https://quay.io/v2/sroze/ft/blobs/uploads/b8f93d65-296c-41ed-9324-3369f69112ee?digest=sha256%3A200140c720609a98e8da53eb9596b732545d9085bfe583dcbd7a5b0503b3415f: Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: read tcp 54.235.117.86:443: use of closed network connection"
    Then the identified error should be a daemon network error
