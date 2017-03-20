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
use Doctrine\ORM\EntityRepository;

class SearchForm extends AbstractType {
  /**
   * @param FormBuilderInterface $builder
   * @param array                $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder->add(
        'text', TextType::class, array(
        'required' => FALSE,
        'label'    => '',
      )
      )->add(
        'tags', EntityType::class, array(
        'class' => 'AppBundle:Tags',

        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
        },
        'choice_label'  => 'name',
        'multiple'      => FALSE,
      )
      )->add(
        'villes', EntityType::class, array(
        'class'         => 'AppBundle:Villes',
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('u')->orderBy('u.name', 'ASC');
        },
        'choice_label'  => 'name',
        'multiple'      => FALSE,
      )
      )->add(
        'search', SubmitType::class, array(
        'label' => FALSE,
      )
      );

  }

}
