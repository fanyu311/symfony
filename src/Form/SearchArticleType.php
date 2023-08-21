<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\User;
use App\Search\SearchArticle;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre:',
                'attr' => [
                    'placeholder' => 'Chercher par titre',
                ],
                'required' => false,
            ])
            // generer le liste dans une talbe (entity) -> entityType
            ->add('tags', EntityType::class, [
                'label' => 'Catégories:',
                'class' => Categorie::class,
                'choice_label' => 'title',
                // chercher custom
                // 寻找到relation的table的内容，按照enable是true的情况下并且是按照从高到低的排序里的所有article排序出来
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('c')
                        ->innerJoin('c.articles', 'a')
                        ->andWhere('c.enable = true')
                        ->orderBy('c.title', 'ASC');
                },
                // affichier comment
                'expanded' => true,
                'multiple' => true,
                // pas obligatoire soumi sur une formulaire
                'required' => false,
            ])
            // ajout un champ
            ->add('authors', EntityType::class, [
                'label' => 'Autheurs:',
                'class' => User::class,
                'choice_label' => 'fullName',
                // ça pour pas le article
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                        // utilisateur pas de article
                        ->innerJoin('u.articles', 'a')
                        ->andWhere('a.enable = true')
                        ->orderBy('u.lastName', 'ASC');
                },
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 链接的哪一个data的class
            'data_class' => SearchArticle::class,
            // 这里的get获得完整的路径-> 为了能够复制链接发送给别人的时候是一个完整的而且是能够找到的
            'method' => 'GET',
            // par default toujour un token-> enleve
            'csrf_protection' => false,
        ]);
    }

    // bloque prefix
    // prend le nom de registrationform
    // nettoyer le url -> le champ nom de prefix (前缀名称字段)
    public function getBlockPrefix(): string
    {
        return '';
    }
}
