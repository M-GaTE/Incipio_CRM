<?php

namespace Tests\Features\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{


    /**
     * You might have to manually create those accounts.
     */
    public $username_password = array('admin' => 'admin', 'moderateur' => 'moderateur', 'user'=>'user');

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $this->visit("/login");
        $this->fillField("Nom d'utilisateur", $username);
        $this->fillField("Mot de passe", $this->username_password[$username]);
        $this->pressButton("Connexion");
    }

    /**
     * @BeforeSuite
     */
    public static function prepare(BeforeSuiteScope $scope)
    {
        exec('php bin/console doctrine:fixtures:load -n -e test');
    }

    /**
     * @AfterScenario @database
     */
    public static function cleanUp(AfterScenarioScope $scope)
    {
        exec('php bin/console doctrine:fixtures:load -n -e test');
    }


    /** @AfterStep */
    public function afterStep(AfterStepScope $event)
    {
        if (!$event->getTestResult()->isPassed()) {
            $this->showLastResponse();
        }
    }

    /**
     * @Given /^I see the "([^"]*)" etude page$/
     */
    public function iSeeTheEtudePage($name)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $repository = $doctrine->getRepository('Mgate\EtudeBundle\Entity\Etude');

        $etude = $repository->findOneBy(array(
            'nom' => $name
        ));

        $this->visit($this->getContainer()->get('router')->generate('MgateSuivi_etude_voir', array('nom' => $etude->getNom())));
    }

    /**
     * @Given /^the user "([^"]*)" is active$/
     */
    public function theUserIsActive($username)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $repository = $doctrine->getRepository('Mgate\UserBundle\Entity\User');

        $user = $repository->findOneBy(array(
            'username' => $username
        ));
        $user->setEnabled(true);

        $doctrine->getManager()->flush();
    }

}
