Feature:
  In order to deploy application in part of a tide
  The images found in application code should be built

  Scenario:
    When a build task is started
    Then it should build the application images

  Scenario:
    Given there is 2 application images in the repository
    When a build task is started
    Then it should build the 2 application images

  Scenario: If a build fails, the task should fail at the same time
    When a build task is started
    And an image build was started
    And the build is failing
    Then the build task should be failed

  Scenario:
    Given there is 1 application images in the repository
    When a build task is started
    And the build succeed
    Then the build task should be successful

  Scenario: The build of images wait all the images to be built
    Given there is 2 application images in the repository
    When a build task is started
    And one image build is successful
    Then the build task should be running

  Scenario: The build of images is failed if one of the image build failed
    Given there is 2 application images in the repository
    When a build task is started
    And one image build is successful
    And one image build is failed
    Then the build task should be failed

  Scenario: The build of images only succeed if all images are successfully built
    Given there is 2 application images in the repository
    And a build task is started
    When 2 image builds are successful
    Then the image should be successfully built
    And the build task should be successful

  Scenario: If a notification for the same successful build is sent, it should consider all builds as successful
    Given there is 2 application images in the repository
    And a build task is started
    When the first image build is successful
    And the first image build is successful
    Then the image builds should be waiting
