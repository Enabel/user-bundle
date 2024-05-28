<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Components;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

class BaseStatusComponent
{
    #[ExposeInTemplate]
    protected ?string $label = null;
    #[ExposeInTemplate]
    protected string $color = 'bg-light text-dark';
    #[ExposeInTemplate]
    protected ?string $icon = null;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    #[PreMount]
    public function preMount(array $data): array
    {
        // validate data
        $resolver = new OptionsResolver();
        $resolver->setRequired('status');
        $resolver->setAllowedTypes('status', 'string');

        return $resolver->resolve($data);
    }
}
