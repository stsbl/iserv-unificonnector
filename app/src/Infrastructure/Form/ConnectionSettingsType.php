<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Form;

use IServ\UnifiConnector\Application\Configuration\ConnectionSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConnectionSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, ['label' => _('UniFi URL')])
            ->add('username', TextType::class, ['label' => _('Username')])
            ->add('password', PasswordType::class, ['label' => _('Password')])
            ->add('fallbackGroup', TextType::class, ['label' => _('Fallback group')])
            ->add('save', SubmitType::class, ['label' => _('Save'), 'icon' => 'ok', 'attr' => ['class' => 'btn-success']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ConnectionSettings::class]);
    }
}
