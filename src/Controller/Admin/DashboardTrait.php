<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

trait DashboardTrait
{
    public function configureActions(): Actions
    {
        return Actions::new()
            ->add(Crud::PAGE_INDEX, Action::NEW)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setIcon('fa fa-plus')
            )
            ->add(Crud::PAGE_INDEX, Action::EDIT)
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn (Action $action) => $action->setIcon('fa fa-pencil')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn (Action $action) => $action->setIcon('fa fa-eye')
            )
            ->add(Crud::PAGE_INDEX, Action::DELETE)
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action->setIcon('fa fa-trash-o')
            )
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn (Action $action) => $action->setIcon('fa fa-save')
            )
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(
                Crud::PAGE_EDIT,
                Action::INDEX,
                fn (Action $action) => $action->setIcon('fa fa-chevron-left')
            )
            ->add(Crud::PAGE_DETAIL, Action::EDIT)
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                fn (Action $action) => $action->setIcon('fa fa-pencil')
            )
            ->add(Crud::PAGE_DETAIL, Action::DELETE)
            ->update(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn (Action $action) => $action->setIcon('fa fa-trash-o')
            )
            ->add(Crud::PAGE_DETAIL, Action::INDEX)
            ->update(
                Crud::PAGE_DETAIL,
                Action::INDEX,
                fn (Action $action) => $action->setIcon('fa fa-chevron-left')
            )
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn (Action $action) => $action->setIcon('fa fa-save')
            )
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(
                Crud::PAGE_NEW,
                Action::INDEX,
                fn (Action $action) => $action->setIcon('fa fa-chevron-left')
            );
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(10)
            ->setDateIntervalFormat('%%y Year(s) %%m Month(s) %%d Day(s)')
            ->setDateTimeFormat('d/M/Y (H:mm)')
            ->setDateFormat('d/M/Y')
            ->setTimeFormat('H:mm')
            ->setNumberFormat('%.2d');
    }
}
