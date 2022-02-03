<?php

namespace App\Form;

use App\Entity\Color;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('title', TextType::class,[
                'label'=>false,
                'required' => false,
                'attr'=>[
                    'placeholder'=>'Saississez le nom de la couleur'
                ]

            ])
            ->add('colorCode', \Symfony\Component\Form\Extension\Core\Type\ColorType::class)
            ->add('Enregistrer', SubmitType::class)
        ;


    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Color::class

        ]);


    }


}