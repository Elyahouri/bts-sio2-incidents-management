<?php

namespace App\Controller\Backoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LockAccountController extends AbstractController
{
    #[Route('/backoffice/lock/account', name: 'app_backoffice_lock_account')]
    public function index(): Response
    {

        return $this->render('backoffice/lock_account/index.html.twig', [
            'controller_name' => 'LockAccountController',
        ]);
    }
}
