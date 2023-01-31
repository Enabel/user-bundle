<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Iterator;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserTrait
{
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $parameterBag = $this->container->get('parameter_bag');

        return parent::configureUserMenu($user)
            ->setName($user->getDisplayName())
            ->setGravatarEmail($user->getEmail())
            ->addMenuItems([
                MenuItem::linkToCrud(
                    'enabel_user.admin.menu.user.profile',
                    'fa fa-id-card',
                    $parameterBag->get('enabel_user.user_class')
                )
                    ->setAction(Action::DETAIL)->setEntityId($user->getId()),
            ])
        ;
    }

    /**
     * @return Iterator<MenuItemInterface>
     */
    public function userMenuEntry(): iterable
    {
        $parameterBag = $this->container->get('parameter_bag');

        yield MenuItem::section('enabel_user.admin.menu.permissions', 'fas fa-key');
        yield MenuItem::linkToCrud(
            'enabel_user.admin.menu.user',
            'fa fa-user',
            $parameterBag->get('enabel_user.user_class')
        )
            ->setDefaultSort(['lastLoginAt' => 'DESC']);
    }
}
