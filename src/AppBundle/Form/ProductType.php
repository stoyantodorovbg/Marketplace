<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('currency', EntityType::class, [
                'class' => 'AppBundle:Currency',
                'choice_label' => 'name',
                'placeholder' => 'choose currency'
            ])
            ->add('availability')
            ->add('quantity')
            ->add('brand')->add('model')
            ->add('isInPromotion')
            ->add('categories', EntityType::class, [
        'class' => 'AppBundle:Category',
        'choice_label' => 'name',
        'placeholder' => 'choose category',
        'multiple' => true,
        'expanded' => true
    ]       );
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
