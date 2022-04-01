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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie*'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie*',

            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget'=> 'single_text',
                'label' => 'Date limite d\'inscription*',
                'constraints' => [
                    new GreaterThan([
                        "today"
                    ])
                ]
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'html5' => true,
                'label' => 'Nombre de place*',
                'attr' => ['min' => 1, 'max' => 100, 'step' => 1]
            ])
            ->add('duree', NumberType::class, [
                'html5' => true,
                'attr' => ['min' => 0, 'max' => 800, 'step' => 10],
                'label' => 'Durée*'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos*'
            ])
            ->add('ville', EntityType::class, [
                "required" => false,
                'class' => Ville::class,
                'placeholder' => "Veuillez choisir une ville",
                'choice_label' => 'nom',
                'mapped' => false,
                'label' => 'Ville*'
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'placeholder' => 'Veuillez choisir un lieu',
                'choice_label' => 'nom',
                'label' => 'Lieu*'
            ])
            ->add('Enregistrer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-primary col-11 col-md-2 mx-3 my-3']
            ])
            ->add('Publier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-outline-success col-11 col-md-2 mx-3 my-3']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
