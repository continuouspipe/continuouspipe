Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario:
    When a tide is started with a build task
    Then a "Building application images" log should be created

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a build task
    Then a "Building image 'image0'" log should be created under the "Building application images" one

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a build task
    And all the image builds are successful
    Then the "Building application images" log should be successful

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a build task
    And the builds are failing
    Then the "Building application images" log should be failed
