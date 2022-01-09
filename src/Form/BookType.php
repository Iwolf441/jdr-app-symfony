<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',null,['label'=>'Titre'])
            ->add('editor',null,['label'=> 'Editeur'])
            ->add('description', TextareaType::class, ['label'=> 'Description'])
            ->add('releaseDate', DateType::Class, array(
                'widget' => 'choice',
                'years' => range(date('Y')-40, date('Y') + 10),
                'months' => range(1, 12),
                'days' => range(1, 31),
                'label'=> ' Date de sortie'
            ))
            ->add('cover', PhotoType::class,['mapped' => false, 'label'=>'Couverture'])
            ->add('category', EntityType::class, ['class' => Category::class, 'label'=>' catégorie'])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}