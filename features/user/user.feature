Feature: CRUD a user
  I am able to register and connect.

  # the "@createSchema" annotation provided by Behat creates a temporary SQLite database for testing the API
  @createSchema
  Scenario: Registration link is visible
    Given I am on "/"
    Then I should see "Inscription"


  # The "@dropSchema" annotation must be added on the last scenario of the feature file to drop the temporary SQLite database
  @dropSchema
  Scenario: I can register
    Given I am on "/register"
    Then the response status code should be 200
    And I fill in "Login" with "test_user"
    And I fill in "Email" with "test_user"
    And I fill in "Mot de passe" with "test_user"
    And I fill in "fos_user_registration_form_plainPassword_second" with "test_user"
    And I press "_submit"
    Then I should see "L'utilisateur a été créé avec succès"

