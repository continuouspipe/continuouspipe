Feature:
  In order to manage my image storage
  As a developer
  I want to be able to chose how am I tagging the Docker images

  Scenario: Build and pushes the SHA1 tag
    Given I have a "docker-compose.yml" file in my repository that contains:
    """
    app:
        build: .
        volumes:
            - ./:/app
        expose:
            - 80
    """
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    app:
                        image: docker.io/inviqasession/cp-website
                        naming_strategy: sha1
    """
    When a tide is started for the branch "master" and commit "3b0110193e36b317207909163d0a582f6f568cf8"
    Then the image tag "3b0110193e36b317207909163d0a582f6f568cf8" should be built
