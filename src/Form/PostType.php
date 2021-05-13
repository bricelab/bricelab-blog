<?php

namespace App\Form;

use App\Entity\Post;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
//            ->add('slug')
            ->add('summary', TextareaType::class)
            ->add('content', CKEditorType::class)
//            ->add('featuredImage')
//            ->add('publishedAt')
//            ->add('createdAt')
//            ->add('updatedAt')
//            ->add('author')
            ->add('categories', ChoiceType::class, [
                'choices' => [
                    'test1' => 1,
                    'test2' => 2,
                    'test3' => 3,
                ],
                'attr' => [
                    'class' => 'js-select2',
                ],
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('tags')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
