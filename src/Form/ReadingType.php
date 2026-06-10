<?php

namespace App\Form;

use App\Entity\Device;
use App\Entity\Reading;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReadingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value')
            ->add('date', null, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('tags', EntityType::class, [
                'required' => false,
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
            ->add('device', EntityType::class, [
                'required' => true,
                'class' => Device::class,
                'choice_label' => 'id',
                'multiple' => false,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reading::class,
        ]);
    }
}
