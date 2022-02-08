<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['add']==true):
        $builder
            ->add('firstName', TextType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre prénom'
                ]
            ])
            ->add('lastName', TextType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre email'
                ]
            ])
            ->add('password', PasswordType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre mot de passe'
                ]
            ])
            ->add('confirmPassword', PasswordType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Confirmez votre mot de passe'
                ]
            ])
            ->add('streetNumber', NumberType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre numéro de rue'
                ]
            ] )
            ->add('street', TextType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre rue'
                ]
            ])
            ->add('zipCode', NumberType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre code postal'
                ]
            ])
            ->add('city', TextType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre ville de résidence'
                ]
            ])
            ->add('phone', NumberType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre numéro de téléphone'
                ]
            ])
            ->add('username', TextType::class,[
                'required'=>false,
                'label'=>false,
                'attr'=>[
                    'placeholder'=>'Saisissez votre pseudo'
                ]
            ])
            ->add('Enregistrer', SubmitType::class)
        ;

        elseif ($options['edit']):
            $builder
                ->add('firstName', TextType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre prénom'
                    ]
                ])
                ->add('lastName', TextType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre nom'
                    ]
                ])
                ->add('email', EmailType::class, [
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre email'
                    ]
                ])

                ->add('streetNumber', NumberType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre numéro de rue'
                    ]
                ] )
                ->add('street', TextType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre rue'
                    ]
                ])
                ->add('zipCode', NumberType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre code postal'
                    ]
                ])
                ->add('city', TextType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre ville de résidence'
                    ]
                ])
                ->add('phone', NumberType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre numéro de téléphone'
                    ]
                ])
                ->add('username', TextType::class,[
                    'required'=>false,
                    'label'=>false,
                    'attr'=>[
                        'placeholder'=>'Saisissez votre pseudo'
                    ]
                ])
                ->add('Enregistrer', SubmitType::class)
            ;
        endif;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'add'=>false,
            'edit'=>false
        ]);
    }
}
