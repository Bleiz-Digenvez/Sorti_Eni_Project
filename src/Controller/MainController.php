<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Form\RechercheSortieType;
use App\Model\RechercheSortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/home", name="main_")
 */
class MainController extends AbstractController
{
    /**
     * Affichage toutes les sorties (sauf celles à l'état 'Passée')
     * Prise en compte du formulaire de recherche
     * @Route("/", name="home")
     */
    public function home(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $this->cronSimulation($sortieRepository, $etatRepository,$entityManager);

        $recherche= new RechercheSortie();
        $recherche->setParticipant($this->getUser());
        $rechercheSortieForm=$this->createForm(RechercheSortieType::class,$recherche);
        $rechercheSortieForm->handleRequest($request);

        $resultats=$sortieRepository->listSortiesAvecRecherche($recherche);

        if ($rechercheSortieForm->isSubmitted()) {
            $resultats=$sortieRepository->listSortiesAvecRecherche($recherche);
            //dd($recherche);
        }
        return $this->render('main/home.html.twig', [
            'rechercheSortieForm' => $rechercheSortieForm->createView(),
            'resultats'=>$resultats,
        ]);
    }

    public function cronSimulation(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager){
        $this->passagePasse($sortieRepository, $etatRepository, $entityManager);
        $this->passageActiviteEnCours($sortieRepository, $etatRepository, $entityManager);
        $this->passageCloturee($sortieRepository, $etatRepository, $entityManager);
    }



    public function passagePasse(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager)
    {
        $etatPasser = $etatRepository->findOneBy(['libelle' => 'Passée']);

        $sorties = $sortieRepository->listSortiesAMettreEnPasse();
        foreach($sorties as $sortie){
            $sortie->setEtat($etatPasser);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
    }
    public function passageActiviteEnCours(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager)
    {
        $etatPasser = $etatRepository->findOneBy(['libelle' => 'Activité en cours']);

        $sorties = $sortieRepository->listSortiesAMettreEnActiviteeEnCours();
        foreach($sorties as $sortie){
            $sortie->setEtat($etatPasser);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
    }
    public function passageCloturee(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager)
    {
        $etatPasser = $etatRepository->findOneBy(['libelle' => 'Cloturée']);

        $sorties = $sortieRepository->listSortiesAMettreEnCloturee();
        foreach($sorties as $sortie){
            $sortie->setEtat($etatPasser);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
    }
}
