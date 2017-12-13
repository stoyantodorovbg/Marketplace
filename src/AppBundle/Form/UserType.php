<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['label' => ' '])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The passwords mismatch',
                'required' => true,
                'first_options' => ['label' => 'Password:'],
                'second_options' => ['label' => 'Repeat password:']
            ])
            ->add('firstName', TextType::class, ['label' => ' '])
            ->add('lastName', TextType::class, ['label' => ' '])
            ->add('phone', TextType::class, ['label' => ' '])
            ->add('company', TextType::class, ['label' => ' '])
            ->add('country', TextType::class, ['label' => ' '])
            ->add('region', TextType::class, ['label' => ' '])
            ->add('town', TextType::class, ['label' => ' '])
            ->add('zipCode', TextType::class, ['label' => ' '])
            ->add('address', TextType::class, ['label' => ' ']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }


}
