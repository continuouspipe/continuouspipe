Feature:
  In order to have a granular configuration
  As a developer
  In want to be able to store my configuration under the same way both in a file in the repository and on CP side

  Scenario: The configuration is loaded from the file stored in the repository
    Given there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - build: ~
    """
    When a tide is started
    Then the build task should be running

  Scenario: When the deploy task is missing some arguments, then the tide should be failed
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kubernetes:
            deploy: ~
    """
    When a tide is started
    Then the tide should be failed

  Scenario: The configuration should be merged with the one stored on CP's side
    Given I have a flow with the following configuration:
    """
    tasks:
        kubernetes:
            deploy:
                cluster: foo/bar
                services: []
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services: []
    """
    When a tide is created
    Then the configuration of the tide should contain at least:
    """
    tasks:
        images:
            build: {}

        kubernetes:
            deploy:
                cluster: foo/bar
    """

  Scenario: The configuration in the repository file is more important
    Given I have a flow with the following configuration:
    """
    tasks:
        named:
            deploy:
                cluster: foo/bar
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        named:
            deploy:
                cluster: bar/baz
                services: []
    """
    When a tide is created
    Then the configuration of the tide should contain at least:
    """
    tasks:
        named:
            deploy:
                cluster: bar/baz
    """

  Scenario: The configuration can be fully configured on the CP's side
    Given there is 1 application images in the repository
    Given I have a flow with the following configuration:
    """
    tasks:
        foo:
            build: ~
    """
    When a tide is started
    Then the build task should be running

  Scenario: The configuration is not valid if many task configuration are in the same task
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - deploy:
              cluster: foo
              services: []
          build:
              services: []
    """
    When a tide is started
    Then the tide should be failed

  Scenario: The YAML file is not valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        foo: 1234
            bar: baz
    """
    When a tide is started
    Then the tide should be failed

  Scenario: Image name in the YAML file is valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: registry.io/organisation/repository-for-something.1
                        tag: 1.0.0
    """
    When a tide is started
    Then the build task should be running

  Scenario: Image name in the YAML file is not valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: ${invalid-image-name}
                        tag: 1.0.0
    """
    When a tide is started
    Then the tide should be failed
    And a log containing 'The name "${invalid-image-name}" of the Docker image is invalid.' should be created

  Scenario: Image name in the YAML file is not valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: registry.co/something-without-organisation
    """
    When a tide is started
    Then the tide should be failed

  Scenario: Image tag in the YAML file is not valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: foo/bar
                        tag: .1.0.0
    """
    When a tide is started
    Then the tide should be failed
    And a log containing 'The tag ".1.0.0" of the Docker image is invalid.' should be created

  Scenario: Image tag in the YAML file is not valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: foo/bar
                        tag: 1.0/0
    """
    When a tide is started
    Then the tide should be failed
