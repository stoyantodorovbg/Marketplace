<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Template;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => ' '])
            ->add('description', TextareaType::class, ['label' => ' '])
            ->add('price', TextType::class, ['label' => ' '])
            ->add('currency', EntityType::class, [
                'class' => 'AppBundle:Currency',
                'choice_label' => 'name',
                'placeholder' => 'choose currency',
                'label' => ' '
            ])
            ->add('availability', CheckboxType::class, [
                'label' => ' ',
                'required'   => false
            ])
            ->add('quantity', TextType::class, ['label' => ' '])
            ->add('brand', TextType::class, [
                'label' => ' ',
                'required'   => false
            ])
            ->add('model', TextType::class, [
                'label' => ' ',
                'required'   => false
            ])
            ->add('isInPromotion', CheckboxType::class, [
                'label' => ' ',
                'required'   => false
            ])
            ->add('categories', EntityType::class, [
                'class' => 'AppBundle:Category',
                'choice_label' => 'name',
                'placeholder' => 'choose category',
                'multiple' => true,
                'expanded' => true,
                'label' => ' '
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Product'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_product';
    }


}
