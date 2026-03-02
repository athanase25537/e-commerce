<?php

namespace App\Form;

use App\Entity\Coupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'attr' => [
                    'placeholder' => 'WELCOME10',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Pourcentage' => 'percentage',
                    'Montant fixe' => 'fixed',
                ],
            ])
            ->add('value', IntegerType::class, [
                'label' => 'Valeur',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('usageLimit', IntegerType::class, [
                'label' => 'Limite d\'utilisation',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Debut',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Fin',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupon::class,
        ]);
    }
}
