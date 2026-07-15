<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Form;

use IServ\Bundle\Autocomplete\Domain\AutocompleteType;
use IServ\Bundle\Autocomplete\Form\Type\AutocompleteTagsType;
use IServ\Bundle\Form\Form\Type\ComboboxType;
use IServ\UnifiConnector\Application\Mapping\MappingSettings;
use IServ\UnifiConnector\Unifi\UserGroup\UserGroupRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextType as CoreTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/** @psalm-suppress MissingTemplateParam Symfony's generic FormType stub is not shared by PHPStan. */
final class MappingSettingsType extends AbstractType
{
    public function __construct(private readonly RouterInterface $router, private readonly UserGroupRepository $userGroups)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', ComboboxType::class, ['label' => _('UniFi group'), 'choices' => $this->groupChoices(), 'placeholder' => _('Choose a UniFi group')])
            ->add('name', CoreTextType::class, ['label' => _('Name'), 'required' => true])
            ->add('priority', IntegerType::class, ['label' => _('Priority'), 'required' => true])
            ->add('subjects', AutocompleteTagsType::class, [
                'label' => _('Users and groups'),
                'help' => _('Selected users and members of selected groups are synchronized to this UniFi group.'),
                'autocomplete_types' => [AutocompleteType::USER_UUID, AutocompleteType::GROUP_UUID],
                'tag_source' => $this->router->generate('unificonnector_admin_autocomplete') . '?type=userid%2Cgroupid',
                'autocomplete_lookup_url' => $this->router->generate('unificonnector_admin_autocomplete'),
                'multiple' => true,
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => _('Add mapping')]);
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
        $resolver->setDefaults(['data_class' => MappingSettings::class]);
    }
}
