<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Form;

use IServ\Bundle\Form\Form\Type\ComboboxType;
use IServ\UnifiConnector\Application\Configuration\ConnectionSettings;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @psalm-suppress MissingTemplateParam Symfony's generic FormType stub is not shared by PHPStan. */
final class ConnectionSettingsType extends AbstractType
{
    public function __construct(private readonly UserGroupRepository $userGroups)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, ['label' => _('UniFi URL')])
            ->add('authenticationMode', ChoiceType::class, ['label' => _('Authentication'), 'choices' => [_('Username and password') => 'password', _('API key') => 'api_key']])
            ->add('username', TextType::class, ['label' => _('Username'), 'required' => false, 'empty_data' => ''])
            ->add('password', PasswordType::class, ['label' => _('Password'), 'required' => false, 'empty_data' => ''])
            ->add('apiKey', PasswordType::class, ['label' => _('API key'), 'required' => false, 'empty_data' => ''])
            ->add('fallbackGroup', ComboboxType::class, [
                'label' => _('Fallback group'),
                'choices' => $this->groupChoices(),
                'placeholder' => _('Choose a UniFi group'),
            ])
            ->add('save', SubmitType::class, ['label' => _('Save'), 'attr' => ['class' => 'btn-success']])
        ;
    }

    /** @return list<string> */
    private function groupChoices(): array
    {
        try {
            $choices = [];
            foreach ($this->userGroups->all() as $group) {
                $choices[] = $group->getName();
            }

            return $choices;
        } catch (\Throwable) {
            return [];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ConnectionSettings::class]);
    }
}
