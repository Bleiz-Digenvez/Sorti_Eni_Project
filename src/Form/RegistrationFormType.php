<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
                'label' => 'Pseudo*'
            ])
            ->add('nom', null, [
                'required' => true,
                'label' => 'Nom*'
            ])
            ->add('prenom', null, [
                'required' => true,
                'label' => 'Prénom*'
            ])
            ->add('telephone', null, [
                'label' => 'Téléphone'
            ])
            ->add('mail', null, [
                'required' => true,
                'label' => 'Adresse Email*'
            ])
            ->add('administrateur',ChoiceType::class,[
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'label' => 'Administrateur',
                'expanded' => true,
                'data' =>false
            ])
            ->add('actif',ChoiceType::class,[
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'label' => 'Actif',
                'expanded' => true,
                'data' => false
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus*'
            ])
            ->add('plainPassword', PasswordType::class,[
                'mapped' => false,
                'attr' =>[
                        'class' => 'password-field',
                        'autocomplete' => 'new-password'],
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
                'label' => 'Mot de Passe*'
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
