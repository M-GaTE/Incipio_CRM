<?php

use Mgate\DashboardBundle\Command\CreateDataCommand;
use Mgate\UserBundle\DataFixtures\ORM\LoadAdminData;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    use KernelDictionary;

    const DEFAULT_USERS = array(
        'admin' => array('username' => 'Load', 'password' => 'admin', 'roles'=> array('ROLE_ADMIN')),
    );

    /**
     * @var array
     */
    private $classes;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public $username_password = array('admin' => 'admin', 'moderateur' => 'moderateur', 'user' => 'user');

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     * @param ManagerRegistry $doctrine
     * @param KernelInterface $kernel
     */
    public function __construct(ManagerRegistry $doctrine, KernelInterface $kernel)
    {
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();

        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario @createSchema
     */
    public function createDatabase()
    {
        $this->schemaTool->createSchema($this->classes);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);


        $input = new ArrayInput(array(
            'command' => 'doctrine:fixtures:load',
            '-n' => true,
            '-e' => 'test',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

    }

    /**
     * @AfterScenario @dropSchema
     * Beware : the annotation in cucumber should also match the case (dropschema is invalid)
     */
    public function dropDatabase()
    {
        $this->schemaTool->dropSchema($this->classes);
    }


    /** @AfterStep */
    public function afterStep(AfterStepScope $event)
    {
        if (!$event->getTestResult()->isPassed()) {
            //$this->printLastResponse();
        }
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $this->visit("/login");
        $this->fillField("_username", $username);
        $this->fillField("_password", self::DEFAULT_USERS[$username]['password']);
        $this->pressButton("Connexion");
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


