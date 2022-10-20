<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    public function login(): Response
    {
        return $this->json([
            'username' => 'jane.doe',
            'password' => '***',
        ]);
    }
}
