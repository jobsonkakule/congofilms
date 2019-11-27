<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password')
            ->add('email')
            ->add('roles', ChoiceType::class, [
                    'choices' => $this->getRoles(),
                    'expanded' => true,
                    'multiple' => true

                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    private function getRoles()
    {
        $output = [
            'Utilisateur' => 'ROLE_USER' ,
            'Redacteur' => 'ROLE_ADMIN',
            'Administrateur' => 'ROLE_SUPER_ADMIN'
        ];
        return $output;
    }
}
