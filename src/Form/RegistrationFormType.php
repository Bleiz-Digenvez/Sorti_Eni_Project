<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', null, [
                'required' => true,
                'label' => 'Pseudo* :'
            ])
            ->add('nom', null, [
                'required' => true,
                'label' => 'Nom* :'
            ])
            ->add('prenom', null, [
                'required' => true,
                'label' => 'Prénom* :'
            ])
            ->add('telephone', null, [
                'label' => 'Téléphone :',
            ])
            ->add('mail', null, [
                'required' => true,
                'label' => 'Adresse Email* :',
            ])
            ->add('administrateur',null,[
                'label' => 'Compte Admin :'
            ])
            ->add('actif',null,[
                'label' => 'Compte Actif :'
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus* :'
            ])
            ->add('plainPassword', RepeatedType::class,[
                'mapped' => false,
                'type' => PasswordType::class,
                'options'=>[
                    'attr' =>[
                        'class' => 'password-field',
                        'autocomplete' => 'new-password'
                    ]],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options' => ['label' => 'Mot de Passe* :'],
                'second_options' => ['label' => 'Confirmation Mot de Passe* :'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
