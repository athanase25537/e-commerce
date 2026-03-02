<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'Ex: Sarah Martin',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'exemple@email.com',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telephone',
                'required' => false,
                'attr' => [
                    'placeholder' => '+33 6 12 34 56 78',
                ],
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => '12 rue des Lilas',
                ],
            ])
            ->add('addressLine2', TextType::class, [
                'label' => 'Complement',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Batiment, etage, etc.',
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => 'France',
                ],
            ])
            ->add('shippingMethod', ChoiceType::class, [
                'label' => 'Livraison',
                'choices' => [
                    'Standard (2-4 jours)' => 'standard',
                    'Express (24-48h)' => 'express',
                ],
                'expanded' => false,
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Paiement',
                'choices' => [
                    'Carte bancaire' => 'card',
                    'Paiement a la livraison' => 'cod',
                ],
                'expanded' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
