Feature:
  In order to be able to protect secrets and build tiny images
  As a user
  I want to be able to have a build in many different steps

  Scenario: Uses read & write artifacts
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        steps:
                            - docker_file_path: ./Buildfile
                              write_artifacts:
                                  - name: built-files
                                    path: /dist

                            - docker_file_path: ./Dockerfile
                              image: sroze/image
                              read_artifacts:
                                  - name: built-files
                                    path: /var/www/html
    """
    When a tide is started with the UUID "00000000-0000-0000-0000-000000000000"
    Then the build should be started with 2 steps
    And the step #0 of the build should be started with a write artifact identified "00000000-0000-0000-0000-000000000000-built-files" on path "/dist"
    And the step #1 of the build should be started with a read artifact identified "00000000-0000-0000-0000-000000000000-built-files" on path "/var/www/html"

  Scenario: Use artifacts as cache folders with the single step configuration
    Given I have a flow with UUID "11111111-1111-1111-1111-111111111111"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        image: sroze/image
                        build_directory: ./sub-directory
                        docker_file_path: ./foo/Dockerfile-bar
                        cache:
                           - /app/vendor
    """
    When a tide is started with the UUID "00000000-0000-0000-0000-000000000000"
    Then the build should be started with 1 steps
    And the step #0 of the build should be started with a persistent write artifact identified "11111111-1111-1111-1111-111111111111-a8561cc8884e7aebc95d469fbc691b56" on path "/app/vendor"
    And the step #0 of the build should be started with a persistent read artifact identified "11111111-1111-1111-1111-111111111111-a8561cc8884e7aebc95d469fbc691b56" on path "/app/vendor"

  Scenario: Use cache with an identifier to allow isolation
    Given I have a flow with UUID "11111111-1111-1111-1111-111111111111"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        - name: CODE_REFERENCE_BRANCH_NAME
          expression: code_reference.branch

    tasks:
        images:
            build:
                services:
                    first:
                        image: sroze/image
                        build_directory: ./sub-directory
                        docker_file_path: ./foo/Dockerfile-bar
                        cache:
                           - identifier: my-app-${CODE_REFERENCE_BRANCH_NAME}
                             path: /app/vendor
    """
    When a tide is started with the UUID "00000000-0000-0000-0000-000000000000" and branch "foo"
    Then the build should be started with 1 steps
    And the step #0 of the build should be started with a persistent write artifact identified "11111111-1111-1111-1111-111111111111-my-app-foo" on path "/app/vendor"
    And the step #0 of the build should be started with a persistent read artifact identified "11111111-1111-1111-1111-111111111111-my-app-foo" on path "/app/vendor"

  Scenario: Use cache with the build steps
    Given I have a flow with UUID "11111111-1111-1111-1111-111111111111"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        steps:
                            - docker_file_path: ./Buildfile
                              cache:
                              - /app/node_modules
                              write_artifacts:
                                  - name: built-files
                                    path: /dist

                            - docker_file_path: ./Dockerfile
                              image: sroze/image
                              read_artifacts:
                                  - name: built-files
                                    path: /var/www/html
    """
    When a tide is started with the UUID "00000000-0000-0000-0000-000000000000"
    Then the build should be started with 2 steps
    And the step #0 of the build should be started with a write artifact identified "00000000-0000-0000-0000-000000000000-built-files" on path "/dist"
    And the step #0 of the build should be started with a persistent write artifact identified "11111111-1111-1111-1111-111111111111-2ad5d7cee913d733ad04aafde20a26db" on path "/app/node_modules"
    And the step #0 of the build should be started with a persistent read artifact identified "11111111-1111-1111-1111-111111111111-2ad5d7cee913d733ad04aafde20a26db" on path "/app/node_modules"

  Scenario: A build with cache is successful
    Given I have a flow with UUID "11111111-1111-1111-1111-111111111111"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        steps:
                            - docker_file_path: ./Buildfile
                              cache:
                              - /app/node_modules
                              write_artifacts:
                                  - name: built-files
                                    path: /dist

                            - docker_file_path: ./Dockerfile
                              image: sroze/image
                              read_artifacts:
                                  - name: built-files
                                    path: /var/www/html
    """
    When a tide is started with the UUID "00000000-0000-0000-0000-000000000000"
    And the first image build is successful
    Then the tide should be successful
