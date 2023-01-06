<?php

declare(strict_types=1);

namespace AdeoWeb\SyliusEveryPayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class EveryPayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('api_url', UrlType::class, [
                'label' => 'adeoweb_sylius_everypay_plugin.configuration.api_url',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
            ])
            ->add('processing_account', TextType::class, [
                'label' => 'adeoweb_sylius_everypay_plugin.configuration.processing_account',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
            ])
            ->add('api_username', TextType::class, [
                'label' => 'adeoweb_sylius_everypay_plugin.configuration.api_username',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
            ])
            ->add('api_secret', TextType::class, [
                'label' => 'adeoweb_sylius_everypay_plugin.configuration.api_secret',
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
            ])
        ;
    }
}
