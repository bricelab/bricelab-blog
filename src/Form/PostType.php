<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\UX\Dropzone\Form\DropzoneType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'mb-3',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Liste des catégories',
                'class' => Category::class,
//                'choices' => $this->repository->findBy([], ['nom' => 'ASC']),
                'attr' => [
                    'class' => 'mb-3 js-select2',
                ],
                'multiple' => true,
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Liste des mots clés',
                'class' => Tag::class,
//                'choices' => $this->repository->findBy([], ['nom' => 'ASC']),
                'attr' => [
                    'class' => 'mb-3 js-select2',
                ],
                'multiple' => true,
                'label_attr' => [
                    'class' => 'mt-3 fw-bold',
                ]
            ])
            ->add('featuredImage', DropzoneType::class, [
                'label' => 'Image à la une',
                'attr' => [
                    'class' => 'mb-3',
                    'placeholder' => 'Glissez & déposez ou Cliquez pour sélectionner',
                ],
                'label_attr' => [
                    'class' => 'mt-3 fw-bold',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'mapped' => false,
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé succinct',
                'attr' => [
                    'class' => 'mb-3',
                    'rows' => 3,
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
                'label_attr' => [
                    'class' => 'mt-3 fw-bold',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'mb-3',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
                'label_attr' => [
                    'class' => 'fw-bold',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
