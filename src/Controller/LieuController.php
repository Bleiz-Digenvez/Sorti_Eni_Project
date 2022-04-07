<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lieu", name="lieu_")
 */
class LieuController extends AbstractController
{
    /**
     * Fonction qui permet de créer des nouveaux lieux
     * @Route("/liste", name="nouveau", host="sortir.com")
     */
    public function nouveau(LieuRepository $lieuRepository, Request $request, EntityManagerInterface $em): Response
    {
        $lieu = new Lieu();
        //Création du formulaire d'ajout des lieux
        $formLieu = $this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);

        if ($formLieu->isSubmitted() && $formLieu->isValid()){
            //Si le formulaire est valide on envoie l'objet Lieu en base de données
            $em->persist($lieu);
            $em->flush();
            //Création du message flash et redirection vers la page d'accueil
            $this->addFlash("success", $lieu->getNom() . " a bien été ajouté aux lieux");
            return $this->redirectToRoute('sortie_creation');
        }
        return $this->render('lieu/nouveau.html.twig', [
            'formLieu' => $formLieu->createView()
        ]);
    }
}
