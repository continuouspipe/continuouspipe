Feature:
  In order to manage my image storage
  As a developer
  I want to be able to chose how am I naming & tagging the Docker images

  Background:
    Given the team "samuel" exists

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

  Scenario: It should automatically guess my registry & username
    Given the team "samuel" have the credentials of a Docker registry "docker.io" with the username "samuel"
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    app:
                        image: cp-website
    """
    When a tide is started for the branch "master"
    Then the build should be started with the image name "docker.io/samuel/cp-website"

  Scenario: It should automatically guess my registry
    Given the team "samuel" have the credentials of a Docker registry "docker.io" with the username "samuel"
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    app:
                        image: sroze/cp-website
    """
    When a tide is started for the branch "master"
    Then the build should be started with the image name "docker.io/sroze/cp-website"

  Scenario: It won't guess or replace anything
    Given the team "samuel" have the credentials of a Docker registry "docker.io" with the username "samuel"
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    app:
                        image: sroze/cp-website
    """
    When a tide is started for the branch "master"
    Then the build should be started with the image name "docker.io/sroze/cp-website"

  Scenario: It automatically guess my full Docker image name with a full registry address with attributres
    Given the team "samuel" have the credentials of the following Docker registry:
      | full_address    | attributes                                       |
      | quay.io/foo/bar | {"flow": "00000000-0000-0000-0000-000000000000"} |
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" and the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    app:
                        image: ~
    """
    When a tide is started for the branch "master"
    Then the build should be started with the image name "quay.io/foo/bar"
