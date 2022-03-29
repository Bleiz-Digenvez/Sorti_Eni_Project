<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
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
            ->add('oldPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
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
                'label' => 'Mot de passe actuel* :'
            ])
            ->add('newPassword', RepeatedType::class,[
                'mapped' => false,
                'type' => PasswordType::class,
                'options'=>[
                    'attr' =>[
                        'class' => 'password-field',
                        'autocomplete' => 'new-password'
                    ]],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options' => ['label' => 'Nouveau Mot de Passe :'],
                'second_options' => ['label' => 'Confirmation Nouveau Mot de Passe :'],
                'required' => false
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
