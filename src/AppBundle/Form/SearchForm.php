<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SearchForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('text',     TextType::class, array(
          'required' => false,
          'label' => '',
        ))
        ->add('tags', EntityType::class, array(
          'class'        => 'AppBundle:Tags',
          'choice_label' => 'name',
          'multiple'     => false,
        ))
        ->add('villes', EntityType::class, array(
          'class'        => 'AppBundle:Villes',
          'choice_label' => 'name',
          'multiple'     => false,
        ))
        ->add('search', SubmitType::class, array(
          'label' => false,
        ));
    }

}
