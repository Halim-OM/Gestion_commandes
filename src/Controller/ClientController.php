<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Form\ClientType;


final class ClientController extends AbstractController
{
    #[Route('/client', name: 'app_client')]
    public function index(ClientRepository $repo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_accueil');
        }
        
        return $this->render('client/index.html.twig', [
            'clients' => $repo->findAll(),
        ]);
    }

    #[Route('/client/{id}', name: 'app_client_show', requirements:['id'=>'\\d+'], methods: ['GET'] )]
    public function show(int $id, ClientRepository $repo):Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_accueil');
        }

        $client = $repo->find($id);
        if(!$client) {
            throw $this->createNotFoundException('Client introuvable');
        }
        return $this->render('client/show.html.twig', ['client' => $client]);
    }

    #[Route('/client/ajouter', name: 'app_client_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em):Response {
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_accueil');
        }
    
        $client = new Client();

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();

            return $this->redirectToRoute('app_client');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/clients/{id}/modifier', name: 'app_client_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id,
    ClientRepository $repository,
    Request $request,
    EntityManagerInterface $em): Response {
    
    if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_accueil');
    }
    
    $client = $repository->find($id);
    if (!$client) {
        throw $this->createNotFoundException('Client introuvable');
    }

    // Le même formulaire, prérempli avec le client existant.
    $form = $this->createForm(ClientType::class, $client);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        return $this->redirectToRoute('app_client');
    }

    return $this->render('client/edit.html.twig', [
        'form' => $form->createView(),
        'client' => $client,
    ]);
    }


    #[Route('/clients/{id}/supprimer', name: 'app_client_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(int $id, ClientRepository $repository, Request $request, EntityManagerInterface $em): Response {
    
    if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page.');
            return $this->redirectToRoute('app_accueil');
    }
    
    $client = $repository->find($id);

    if (!$client) {
        throw $this->createNotFoundException('Client introuvable');
    }

    if (!$this->isCsrfTokenValid(
        'delete_client_'.$client->getId(),
        $request->request->get('_token')
    )) {
        
        throw $this->createAccessDeniedException('Jeton invalide');
    }

    if (!$client->getCommandes()->isEmpty()) {
        $this->addFlash(
            'error',
            'Impossible de supprimer un client ayant des commandes.'
        );

        return $this->redirectToRoute('app_client');
    }

    $em->remove($client);
    $em->flush();

    $this->addFlash('success', 'Client supprimé.');

    return $this->redirectToRoute('app_client');
}

}

