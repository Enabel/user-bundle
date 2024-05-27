<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Enabel\User;
use App\Form\ChangePassword;
use App\Form\UserProfile;
use App\Repository\Enabel\UserRepository;
use App\Service\Account\ChangePassword as AccountChangePassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account', name: 'account_')]
class AccountController extends AbstractController
{
    private const LENGTH = 10;
    private const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*$%+';

    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('account/profile.html.twig');
    }

    #[Route('/edit', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserProfile::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'flash.editProfile.success');

            return $this->redirectToRoute('account_profile', ['_locale' => $user->getLocale()]);
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/password', name: 'change_password')]
    public function changePassword(Request $request, AccountChangePassword $changePassword): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePassword::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from form
            $data = $form->getData();

            // Invoke change password service
            if ($changePassword($user, $data['old-password'], $data['password'])) {
                $this->addFlash('success', 'flash.passwordChange.success');
                return $this->redirectToRoute('account_profile');
            }

            $this->addFlash('danger', 'flash.passwordChange.error');
        }

        return $this->render('account/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * See route.yaml
     */
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        if ($request->request->get('forgotPasswordEmail', '')) {
            /** @var string $email */
            $email = $request->request->get('forgotPasswordEmail', '');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $password = $this->generatePassword();
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $entityManager->persist($user);
                $entityManager->flush();

                $this->sendNewPassword($user, $password);

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('account/forgotPassword.html.twig');
    }

    public function sendNewPassword(User $user, string $password): TemplatedEmail
    {
        /** @var string $address */
        $address = $user->getEmail();

        $email = (new TemplatedEmail())
            ->to($address)
            ->subject('[Enabel] New password request')
            ->htmlTemplate('emails/new.password.html.twig')
            ->context([
                'user' => $user,
                'password' => $password,
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            echo 'An error has occurred while sending email: ' . $e->getMessage();
        }

        return $email;
    }

    public function generatePassword(): string
    {
        $charactersLength = strlen(self::CHARACTERS);
        $randomPassword = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $randomPassword .= self::CHARACTERS[random_int(0, $charactersLength - 1)];
        }
        return $randomPassword;
    }
}
