<?php

namespace App\Form;

use App\Entity\Calendar;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CalendarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'] ?? false;

        $builder
            ->add('title', TextType::class, [
                'disabled' => $isAdmin && $options['edit_mode'], // Désactiver pour les admins en mode édition
            ])
            ->add('start', DateTimeType::class, [
                'disabled' => $isAdmin && $options['edit_mode'], // Désactiver pour les admins en mode édition
            ])
            ->add('end', DateTimeType::class, [
                'disabled' => $isAdmin && $options['edit_mode'], // Désactiver pour les admins en mode édition
            ])
            ->add('description', TextType::class, [
                'disabled' => $isAdmin && $options['edit_mode'], // Désactiver pour les admins en mode édition
            ])
            ->add('all_day', CheckboxType::class, [
                'disabled' => $isAdmin && $options['edit_mode'], // Désactiver pour les admins en mode édition
            ]);

        if ($isAdmin) {
            $builder->add('status', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'en cours',
                    'Accepté' => 'accepté',
                    'Refusé' => 'refusé',
                ],
                'required' => false,
                'label' => 'Status',
                'attr' => ['class' => 'form-control'],
                'mapped' => true,
            ])
                ->add('commentaire', TextareaType::class, [
                    'required' => false,
                    'label' => 'Commentaire',
                ]);
        } else {
            $builder->add('status', HiddenType::class, [
                'data' => 'en cours',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Calendar::class,
            'is_admin' => false,
            'edit_mode' => false, // Ajouter une option pour le mode édition
        ]);
    }
}
