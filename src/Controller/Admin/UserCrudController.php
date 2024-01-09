<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Enabel\UserBundle\Controller\Admin\Filter\SoftDeleteFilter;
use Enabel\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminContextProvider $adminContextProvider,
        private ParameterBagInterface $parameterBag,
        private RoleHierarchyInterface $roleHierarchy,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    ) {
        if ($entityManager->getFilters()->isEnabled('softdeleteable')) {
            // disable the softdeleteable filter
            // @codeCoverageIgnoreStart
            $entityManager->getFilters()->disable('softdeleteable');
            // @codeCoverageIgnoreEnd
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(
            SoftDeleteFilter::new(
                'deletedAt',
                $this->translator->trans('enabel_user.admin.form.showDeleted')
            )->setChoiceLabels(
                $this->translator->trans('enabel_user.admin.form.yes'),
                $this->translator->trans('enabel_user.admin.form.no')
            )
        );

        return parent::configureFilters($filters);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'enabel_user.admin.title.manage_users')
            ->setPageTitle(Crud::PAGE_NEW, 'enabel_user.admin.title.new_user')
            ->setPageTitle(Crud::PAGE_EDIT, 'enabel_user.admin.title.edit_user')
            ->setPageTitle(Crud::PAGE_DETAIL, static fn ($entity): string => sprintf(
                '%s<br/><small>%s</small>',
                $entity->getDisplayName(),
                $entity->getEmail()
            ))
            ->setEntityLabelInSingular('enabel_user.admin.title.user')
            ->setEntityLabelInPlural('enabel_user.admin.title.users')
            ->setSearchFields(['id', 'email', 'displayName'])
            ->setEntityPermission(User::ADMIN)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $impersonate = Action::new('impersonate', 'enabel_user.admin.action.impersonate', 'fa fa-user-secret')
            ->linkToCrudAction('impersonate')
            ->setCssClass('btn btn-info')
            ->displayIf(static fn (User $entity): bool => $currentUser->getUserIdentifier() !== $entity->getEmail());
        $actions
            ->add(Crud::PAGE_DETAIL, $impersonate)
            ->add(Crud::PAGE_EDIT, $impersonate)
            ->setPermission('impersonate', 'ROLE_ALLOWED_TO_SWITCH');

        $restoreUser = Action::new('restoreUser', 'enabel_user.admin.action.restore', 'fa fa-undo')
            ->linkToCrudAction('restoreUser')
            ->displayIf(
                static fn (User $entity): bool => $entity->isDeleted()
            );
        $actions
            ->add(Crud::PAGE_DETAIL, $restoreUser)
            ->add(Crud::PAGE_INDEX, $restoreUser)
            ->setPermission('restoreUser', 'ROLE_SUPER_ADMIN');

        $actions->update(
            Crud::PAGE_INDEX,
            Action::DELETE,
            static function (Action $action) use ($currentUser): Action {
                $action->displayIf(
                    static fn (User $entity): bool => $currentUser->getUserIdentifier() !== $entity->getEmail()
                );

                return $action;
            }
        );

        $actions->update(
            Crud::PAGE_DETAIL,
            Action::DELETE,
            static function (Action $action) use ($currentUser): Action {
                $action->displayIf(
                    static fn (User $entity): bool => $currentUser->getUserIdentifier() !== $entity->getEmail()
                );

                return $action;
            }
        );

        return $actions;
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        /** @var AdminContext $context */
        $context = $this->adminContextProvider->getContext();
        /** @var User|null $subject */
        $subject = $context->getEntity()->getInstance();
        /** @var array<string> $roles */
        $roles = $this->parameterBag->get('security.role_hierarchy.roles');
        $rolesChoices = [];
        foreach ($roles as $role => $sub) {
            $rolesChoices[$role] = $role;
        }

        if (!$this->isGranted('ROLE_SUPER_ADMIN') && in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $accessibleRole = $this->roleHierarchy->getReachableRoleNames($currentUser->getRoles());
            $rolesChoices = array_intersect_key($rolesChoices, array_flip($accessibleRole));
        }

        $id = IdField::new('id', 'enabel_user.admin.form.id');
        $email = EmailField::new('email', 'enabel_user.admin.form.email')
            ->setColumns('col-md-6');
        $displayName = TextField::new('displayName', 'enabel_user.admin.form.displayName')
            ->setColumns('col-md-6');
        $jobTitle = TextField::new('jobTitle', 'enabel_user.admin.form.jobTitle')
            ->setColumns('col-md-4');
        $countryWorkplace = CountryField::new('countryWorkplace', 'enabel_user.admin.form.countryWorkplace')
            ->setColumns('col-md-4');
        /** @var string $availableLocales */
        $availableLocales = $this->parameterBag->get('enabel_user.available_locales');
        $locale = LocaleField::new('locale', 'enabel_user.admin.form.locale')
            ->includeOnly(explode('|', $availableLocales))
            ->setColumns('col-md-4');
        $lastLoginAt = DateTimeField::new('lastLoginAt', 'enabel_user.admin.form.lastLoginAt');
        $plainPassword = Field::new('plainPassword', 'enabel_user.admin.form.password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'label' => 'enabel_user.admin.form.password',
                ],
                'second_options' => [
                    'label' => 'enabel_user.admin.form.password.confirm',
                ],
            ])
            ->setColumns('col-md-6');
        if (Crud::PAGE_EDIT === $pageName && $subject !== null) {
            if ($subject->getEmail() !== null && str_contains($subject->getEmail(), '@enabel.be')) {
                $plainPassword->setDisabled(true);
                $plainPassword->setRequired(false);
            }
        }

        $roles = ChoiceField::new('roles', 'enabel_user.admin.form.roles')
            ->setRequired(true)
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setChoices($rolesChoices)
            ->renderAsBadges()
            ->setColumns('col-md-6');
        if (
            Crud::PAGE_EDIT == $pageName
            && $subject !== null
            && $currentUser->getUserIdentifier() === $subject->getEmail()
        ) {
            $roles->setDisabled(true);
        }
        $createdAt = DateTimeField::new('createdAt', 'enabel_user.admin.form.createdAt');
        $updatedAt = DateTimeField::new('updatedAt', 'enabel_user.admin.form.updatedAt');
        $deletedAt = DateTimeField::new('deletedAt', 'enabel_user.admin.form.deletedAt');
        $isDeleted = BooleanField::new('isDeleted', 'enabel_user.admin.form.isDeleted')
            ->renderAsSwitch(false);

        $tabDetails = FormField::addTab('enabel_user.admin.tab.details');
        $panelPersonal = FormField::addFieldset('enabel_user.admin.panel.personal')
            ->setIcon('fa fa-id-card');
        $panelAccount = FormField::addFieldset('enabel_user.admin.panel.account')
            ->setIcon('fa fa-key');
        $tabLog = FormField::addTab('enabel_user.admin.tab.log');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$displayName, $email, $lastLoginAt, $roles,$isDeleted];
        }

        return [
            $tabDetails,
            $panelPersonal,
            $displayName,
            $email,
            $jobTitle,
            $countryWorkplace,
            $locale,
            $panelAccount,
            $id->onlyOnDetail(),
            $plainPassword->onlyOnForms(),
            $roles,
            $tabLog->onlyOnDetail(),
            $lastLoginAt->onlyOnDetail(),
            $createdAt->onlyOnDetail(),
            $updatedAt->onlyOnDetail(),
            $deletedAt->onlyOnDetail(),
        ];
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param User $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $this->container->get('security.password_hasher');
        $plainPassword = $entityInstance->getPlainPassword();
        if ($plainPassword !== null) {
            $password = $hasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($password);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param User $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $this->container->get('security.password_hasher');
        $plainPassword = $entityInstance->getPlainPassword();
        if ($plainPassword !== null) {
            $password = $hasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($password);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function impersonate(AdminContext $context): RedirectResponse
    {
        if (!$this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        /** @var User|null $user */
        $user = $context->getEntity()->getInstance();
        /** @var string $referer */
        $referer = $context->getRequest()->headers->get('referer');
        if ($user) {
            $referer .= $referer .
                (parse_url($referer, PHP_URL_QUERY) ? '&' : '?') .
                '_switch_user=' .
                $user->getUserIdentifier()
            ;
            $this->addFlash('success', $this->translator->trans(
                'enabel_user.admin.flashes.impersonate',
                [
                    '%name%' => $user->getUserIdentifier(),
                ]
            ));
        }

        return new RedirectResponse($referer);
    }

    public function restoreUser(AdminContext $context): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->restore();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        /** @var string $referer */
        $referer = $context->getRequest()->headers->get('referer');
        $this->addFlash('success', $this->translator->trans(
            'enabel_user.admin.flashes.restored',
            [
                '%name%' => $user->getUserIdentifier(),
            ]
        ));

        return $this->redirect($referer);
    }

    /**
     * @return array<string|SubscribedService>
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'security.password_hasher' => '?' . UserPasswordHasherInterface::class,
            TranslatorInterface::class => '?' . TranslatorInterface::class,
            RoleHierarchyInterface::class => '?' . RoleHierarchyInterface::class,
            ParameterBagInterface::class => '?' . ParameterBagInterface::class,
        ]);
    }
}
