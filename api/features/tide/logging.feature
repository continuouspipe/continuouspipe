Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario:
    When a tide is started
    Then a "Building application images" log should be created

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started
    Then a "Building image 'image0'" log should be created under the "Building application images" one
