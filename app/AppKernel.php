<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
			/****************************************
			*				Symfony 				*
			*****************************************/
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
			new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
			/****************************************
			*			Vendor - Doctrine			*
			*****************************************/
			new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
			new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
			/****************************************
			*			Vendor - FOS				*
			*****************************************/
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\CommentBundle\FOSCommentBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            

            
            new Ob\HighchartsBundle\ObHighchartsBundle(),
			new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            
			new JMS\SerializerBundle\JMSSerializerBundle($this),

			/****************************************
			*				M-GaTE					*
			*****************************************/
			new Mgate\UserBundle\MgateUserBundle(),
			new Mgate\PubliBundle\MgatePubliBundle(),
            new Mgate\DashboardBundle\MgateDashboardBundle(),
            new Mgate\StatBundle\MgateStatBundle(),
            new Mgate\TresoBundle\MgateTresoBundle(),
            new Mgate\FormationBundle\MgateFormationBundle(),
			new Mgate\PersonneBundle\MgatePersonneBundle(),
            new Mgate\CommentBundle\MgateCommentBundle(),
			new Mgate\SuiviBundle\MgateSuiviBundle(),
			new JavierEguiluz\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new N7consulting\RhBundle\N7consultingRhBundle(),
            new N7consulting\DevcoBundle\N7consultingDevcoBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
