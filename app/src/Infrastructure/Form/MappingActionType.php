<?php

declare(strict_types=1);

namespace IServ\UnifiConnector\Infrastructure\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MappingActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', HiddenType::class);
        if (null !== $options['direction']) {
            $builder->add('direction', HiddenType::class, ['data' => $options['direction']]);
        }
        $builder->add('submit', SubmitType::class, [
            'label' => $options['label'],
            'attr' => ['icon' => $options['icon']],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['label', 'icon']);
        $resolver->setDefault('direction', null);
        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('icon', 'string');
        $resolver->setAllowedTypes('direction', ['null', 'string']);
    }
}
