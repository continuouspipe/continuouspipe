Feature:
  In order to deploy application in part of a tide
  The images found in application code should be built

  Scenario: When a tide it started, it should try to build application images
    When a tide is started
    Then it should build the application images

  @wip
  Scenario: When a tide it started, it should build all the images found in repository
    Given there is 2 application images in the repository
    When a tide is started
    Then it should build the 2 application images

  @wip
  Scenario: If a build fails, the tide should fail at the same time
    Given an image build was started
    When the build is failing
    Then the tide should be failed

  @wip
  Scenario: The build of images wait all the images to be built
    Given 2 images builds were started
    When one image build is successful
    Then the image builds should be waiting

  @wip
  Scenario: The build of images is failed if one of the image build failed
    Given 2 images builds were started
    When one image build is successful
    And one image build is failed
    Then the tide should be failed

  @wip
  Scenario: The build of images only succeed if all images are successfully built
    Given 2 images builds were started
    When 2 image builds are successful
    Then the image builds should be successful
