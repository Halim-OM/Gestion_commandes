<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Form\CommandeType;

use App\Entity\LigneCommande;
use App\Form\LigneCommandeType;
use App\Repository\LigneCommandeRepository;



final class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function index(CommandeRepository $repo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('commande/index.html.twig', [
            'commandes' => $repo->findAll(),
        ]);
    }


    #[Route('/commande/{id}', name: 'app_commande_show', requirements:['id'=>'\\d+'], methods:['GET'])]
    public function show(int $id, CommandeRepository $repo):Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page.');
            return $this->redirectToRoute('app_login');
        }

        $commande = $repo->find($id);
        if(!$commande){
            throw $this->createNotFoundException('client introuvable');
        }
        return $this->render('commande/show.html.twig', ['commande' => $commande]);
    }


    #[Route('/commande/ajouter', name: 'app_commande_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em):Response {
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_login');
        }
    
        $commande = new Commande();
        $commande->setTotal(0);


        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($commande);
            $em->flush();
            return $this->redirectToRoute('app_commande');
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commandes/{id}/lignes/ajouter', name: 'app_commande_ligne_new', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function ajouterLigne(int $id, CommandeRepository $repository, Request $request, EntityManagerInterface $em): Response {
    
    if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seul admin pourra consulter cette page');
            return $this->redirectToRoute('app_login');
    }

    $commande = $repository->find($id);

    if (!$commande) {
        throw $this->createNotFoundException('Commande introuvable');
    }

    $ligne = new LigneCommande();
    
    //ici on initialise la quantité à 1 pour qu'elle s'affiche au formulaire en tant que valeur initiale
    $ligne->setQuantite(1);

    // La commande est imposée par l’URL, pas choisie dans le formulaire.
    $ligne->setCommande($commande);

    $form = $this->createForm(LigneCommandeType::class, $ligne);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        
        if ($ligne->getQuantite() > $ligne->getProduit()->getStock()) {
            $this->addFlash('error', 'La quantité demandée dépasse le stock disponible.');

            return $this->redirectToRoute('app_commande_ligne_new', [
                'id' => $commande->getId(),
            ]);
        }

        //si non on modifie le stock du produit
        $ligne->getProduit()->setStock($ligne->getProduit()->getStock() - $ligne->getQuantite());
    
        // Conserver le prix du produit au moment de l’ajout.
        $ligne->setPrixUnitaire($ligne->getProduit()->getPrix());

        $ligne->setMontant($ligne->getPrixUnitaire() * $ligne->getQuantite());

        
        $commande->addLigneCommande($ligne);
        $commande->setTotal($commande->getTotal() + $ligne->getMontant());

        $em->persist($ligne);
        $em->flush();

        return $this->redirectToRoute('app_commande_show', [
            'id' => $commande->getId(),
        ]);
    }

    return $this->render('commande/ajouter_ligne.html.twig', [
        'commande' => $commande,
        'form' => $form->createView(),
    ]);
}

}
