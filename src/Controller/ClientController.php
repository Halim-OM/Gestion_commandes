<?php

namespace App\Controller;
use App\Repository\ClientRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(ClientRepository $repo): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $repo->findAll(),
        ]);
    }

    #[Route('/client/{id}', name: 'app_client_show', requirements:['id'=>'\\d+'], methods: ['GET'] )]
    public function show(int $id, ClientRepository $repo):Response
    {
        $client = $repo->find($id);
        if(!$client) {
            throw $this->createNotFoundException('Client introuvable');
        }
        return $this->render('client/show.html.twig', ['client' => $client]);
    }
}

