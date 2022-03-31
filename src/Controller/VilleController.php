<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/home/ville", name="ville_")
 */
class VilleController extends AbstractController
{
    /**
     * @Route("/liste", name="liste")
     */
    public function liste(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        //Récuperation de toutes les villes
        $listeVille = $villeRepository->findAll();

        //Début du formulaire d'ajout de liste
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);
        //Traitement du formulaire
        if ($villeForm->isSubmitted() && $villeForm->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();
            //Ajout du message flash
            $this->addFlash('success', "La ville à bien été ajoutée");
            return $this->redirectToRoute('ville_liste');
        }

        return $this->render('ville/liste.html.twig', [
            'villes' => $listeVille,
            'formVille' => $villeForm->createView()
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="supprimer")
     */
    public function supprimer(Ville $ville ,EntityManagerInterface $entityManager)
    {
        $entityManager->remove($ville);
        $entityManager->flush();
        $this->addFlash('succes', 'La ville est bien supprimée');
        return $this->redirectToRoute('ville_liste');
    }
}
