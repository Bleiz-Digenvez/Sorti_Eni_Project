<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\RechercheVilleType;
use App\Form\VilleType;
use App\Model\RechercheVille;
use App\Repository\VilleRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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

        //Début du formulaire d'ajout de ville
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
        //Fin du traitement formulaire d'ajout de ville
        //Début du formulaire de recherche de ville
        $recherche = new RechercheVille();
        $rechercheForm = $this->createForm(RechercheVilleType::class, $recherche);
        $rechercheForm->handleRequest($request);
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            $listeVille = $villeRepository->rechercheVille($recherche->getNom());
            dump($listeVille);
        }

        return $this->render('ville/liste.html.twig', [
            'villes' => $listeVille,
            'formVille' => $villeForm->createView(),
            'formRecherche' => $rechercheForm->createView()
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="supprimer")
     */
    public function supprimer(Ville $ville ,EntityManagerInterface $entityManager)
    {
        try {
            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('success', 'La ville est bien supprimée');
        } catch (\Exception $ex){
            $this->addFlash("error", "Impossible de supprimer cette ville pour le moments");
            //throw new Exception("Impossible de supprimer cette ville pour le moments");
        }


        return $this->redirectToRoute('ville_liste');
    }
}
