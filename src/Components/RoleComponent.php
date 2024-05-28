<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Components;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @codeCoverageIgnore
 */
#[AsTwigComponent(template: '@EnabelUser/templates/components/user-role.html.twig', name: 'user-role')]
class RoleComponent extends BaseStatusComponent
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function mount(string $status): void
    {
        $this->setOptionsByStatus($status);
    }

    private function setOptionsByStatus(string $status): void
    {
        switch ($status) {
            case 'ROLE_SUPER_ADMIN':
                $this->color = 'bg-info';
                $this->label = $this->translator->trans('ROLE_SUPER_ADMIN');
                break;

            case 'ROLE_ADMIN':
                $this->color = 'bg-info';
                $this->label = $this->translator->trans('ROLE_ADMIN');
                break;

            case 'ROLE_USER':
                $this->color = 'bg-success';
                $this->label = $this->translator->trans('ROLE_USER');
                break;
        }
    }
}
