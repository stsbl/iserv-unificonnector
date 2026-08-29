<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/** @psalm-suppress MissingTemplateParam Symfony's generic FormType stub is not shared by PHPStan. */
final class DeleteApiKeyActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('submit', SubmitType::class, ['label' => _('Delete stored API key'), 'attr' => ['class' => 'btn-danger']]);
    }
}
