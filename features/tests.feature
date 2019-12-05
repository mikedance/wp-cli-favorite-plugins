Feature: Test the WP Plugin Favorites command.

  Scenario: WP-CLI loads for your tests
    Given a WP install

    When I run `wp eval 'echo "Hello world.";'`
    Then STDOUT should contain:
      """
      Hello world.
      """

  Scenario: Get a list of favorited plugins from a specific user account.
    Given a WP install

    When I run `wp plugin favorites johnjamesjacoby`
    Then STDOUT should contain:
      """
      bbpress
      buddypress
      """

    When I run `wp plugin favorites johnjamesjacoby --verbose`
    Then STDOUT should contain:
      """
      BuddyPress
      """

    When I run `wp plugin favorites notarealuser_ewojrnewokrn`
    Then STDOUT should contain:
      """
      No favorite plugins found.
      """
