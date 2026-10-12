<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class LigneCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => fn(Produit $produit)=>$produit->getReference().' '.$produit->getNom(),
            ])
            ->add('quantite', IntegerType::class, [
                'attr' => ['min' => 1],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'La quantité est obligatoire'
                    ),
                    new Assert\Positive(
                        message : 'La quantité doit être positive'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneCommande::class,
        ]);
    }
}
