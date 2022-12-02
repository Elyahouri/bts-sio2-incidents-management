<?php

namespace App\Form;

use App\Entity\Incident;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignIncidentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('createdAt')
            //->add('description')
            //->add('reporterEmail')
            //->add('reference')
            //->add('processedAt')
            //->add('resolveAt')
            //->add('rejectedAt')
            //->add('level')
            //->add('status')
            //->add('types')
            ->add('followedBy', EntityType::class, [
        'class' => User::class,
        'choices' => $options['tech'],
        'choice_label'=> function($user){
                return $user->getEmail();
        },
        'label_attr' => [
            'class' => 'checkbox-inline',
        ],
        'expanded' => true,
    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Incident::class,
            'tech' => []
        ]);
    }
}
