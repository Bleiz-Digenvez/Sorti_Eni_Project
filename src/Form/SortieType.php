<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : '
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie : '
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget'=> 'single_text',
                'label' => 'Date limite d\'inscription : '
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombre de place : '
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée : '
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos : '
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom'
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
