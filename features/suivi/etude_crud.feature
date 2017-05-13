Feature: Etude
  I am able to CRUD an Etude.

  # the "@createSchema" annotation provided by Behat creates a temporary SQLite database for testing the API
  @createSchema
  Scenario: I can see Etude pipeline
    Given I am logged in as "admin"
    When I go to "/suivi"
    Then the response status code should be 200
    Then I should see "Etudes en Négociation"

  # The "@dropSchema" annotation must be added on the last scenario of the feature file to drop the temporary SQLite database
  @dropSchema
  Scenario: Void
    Given I am logged in as "admin"