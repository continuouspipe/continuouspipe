Feature:
  In order to configure CP
  As a developer
  In want to get a documentation about the possible options and values

  Scenario: Generate documentation for configuration schema via CLI
    Given the configuration schema is defined
    When I run the documentation generator console command
    Then I should see the following output:
    """
    # Foo

    Foo is a boolean config option. Defaults to true.

    Examples:

        - foo: false
        - foo: true

    # Bar

    Bar is a scalar config option. Defaults to "baz".

    Example:

        - bar: qwerty

    """
