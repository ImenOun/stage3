<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Permission;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['placeholder' => 'Entrez l\'adresse e-mail']
            ])
            ->add('permissions', EntityType::class, [
                'class' => Permission::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, // => checkboxes
                'label' => 'Privilèges',
            ])
           ->add('password', PasswordType::class, [
                'required' => false, // pour permettre l’édition sans modifier le mot de passe
                'mapped' => true,
                'label' => 'Mot de passe',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le nom']
            ])
         ->add('salaireNet', NumberType::class, [
            'label' => 'Salaire Net',
            'scale' => 2,
            'html5' => true,
            'attr' => [
                'placeholder' => 'Ex: 1200.50',
                'step' => '0.01',
                'min' => '0'
            ],
            'constraints' => [
                new Assert\PositiveOrZero(message: 'Le salaire net doit être positif ou nul.'),
                new Assert\Regex([
                    'pattern' => '/^\d+(\.\d{1,2})?$/',
                    'message' => 'Veuillez entrer un nombre valide avec au maximum 2 décimales.'
                ])
            ],
            'required' => false
        ])
        ->add('salaireBrute', NumberType::class, [
            'label' => 'Salaire Brut',
            'scale' => 2,
            'html5' => true,
            'attr' => [
                'placeholder' => 'Ex: 1500.75',
                'step' => '0.01',
                'min' => '0'
            ],
            'constraints' => [
                new Assert\PositiveOrZero(message: 'Le salaire brut doit être positif ou nul.'),
                new Assert\Regex([
                    'pattern' => '/^\d+(\.\d{1,2})?$/',
                    'message' => 'Veuillez entrer un nombre valide avec au maximum 2 décimales.'
                ])
            ],
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}