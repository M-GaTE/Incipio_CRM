<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PubliBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\PubliBundle\Controller\TraitementController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', ChoiceType::class, array(
            'required' => true,
            'label' => 'Document Type',
            'choices' => array(
                'Fiche de suivi d\'étude' => TraitementController::DOCTYPE_SUIVI_ETUDE,
                'Devis' => TraitementController::DOCTYPE_DEVIS,
                'Avant-Projet' => TraitementController::DOCTYPE_AVANT_PROJET,
                'Convention Client' => TraitementController::DOCTYPE_CONVENTION_CLIENT,
                'Facture d\'acompte' => TraitementController::DOCTYPE_FACTURE_ACOMTE,
                'Facture intermédiaire' => TraitementController::DOCTYPE_FACTURE_INTERMEDIAIRE,
                'Facture de solde' => TraitementController::DOCTYPE_FACTURE_SOLDE,
                'Procès verbal de recette intermédiaire' => TraitementController::DOCTYPE_PROCES_VERBAL_INTERMEDIAIRE,
                'Procès verbal de recette final' => TraitementController::DOCTYPE_PROCES_VERBAL_FINAL,
                'Récapitulatif de mission' => TraitementController::DOCTYPE_RECAPITULATIF_MISSION,
                'Descriptif de mission' => TraitementController::DOCTYPE_DESCRIPTIF_MISSION,
                'Convention Etudiant' => TraitementController::DOCTYPE_CONVENTION_ETUDIANT,
                'Fiche d\'adhésion' => TraitementController::DOCTYPE_FICHE_ADHESION,
                'Accord de confidentialité' => TraitementController::DOCTYPE_ACCORD_CONFIDENTIALITE,
                'Déclaration étudiant étranger' => TraitementController::DOCTYPE_DECLARATION_ETUDIANT_ETR,
                'Note de Frais' => TraitementController::DOCTYPE_NOTE_DE_FRAIS,
                'Bulletin de Versement' => TraitementController::DOCTYPE_BULLETIN_DE_VERSEMENT,
            ),
        ))
            ->add('etudiant', Select2EntityType::class, array(
                'class' => 'Mgate\\PersonneBundle\\Entity\\Membre',
                'choice_label' => 'identifiant',
                'label' => 'Intervenant pour vérifier le template',
                'required' => false,
            ))
            ->add('template', FileType::class, array('required' => true))
            ->add('etude', Select2EntityType::class, array(
                'label' => 'Etude pour vérifier le template',
                'class' => 'Mgate\\SuiviBundle\\Entity\\Etude',
                'choice_label' => 'reference',
                'required' => false, ))
            ->add('verification', CheckboxType::class, array('label' => 'Activer la vérification', 'required' => false));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_doctypetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
        ));
    }
}
