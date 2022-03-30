<?php

namespace App\Form;

//use App\Entity\Site;
use App\Model\RechercheSortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
 //           ->add('site',EntityType::class,[
 //               'class'=>Campus::class,
 //               'choice_label' => 'nom',
 //               'required'=>false
 //           ])
            ->add('site',TextType::class,[
               'label' => 'Campus',
               'required'=>false
           ])
            ->add('nomSortie',TextType::class,[
                'label' => "Le nom de la sortie contient",
                'required'=>false
            ])
            ->add('dateMin',DateType::class,[
                'label' => "entre",
                'required'=>false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('dateMax',DateType::class,[
                'label' => "et",
                'required'=>false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('organisateur',CheckboxType::class,[
                'label' => "Sorties dont je suis l'organisateur/trice",
                'required'=>false
            ])
            ->add('pasInscrit',CheckBoxType::class,[
                'label' => "Sorties auquelles je ne suis pas inscrit/e",
                'required'=>false
            ])
            ->add('inscrit',CheckBoxType::class,[
                'label' => "Sorties auquelles je suis  inscrit/e",
                'required'=>false
            ])
            ->add('passees',CheckboxType::class,[
                'label' => "Sorties passées",
                'required'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RechercheSortie::class,
        ]);
    }
}
