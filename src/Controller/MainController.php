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
     * @Route("/", name="mobile_home", host="{subdomain}.sortir.com", defaults={"subdomain"="m"}, requirements={"subdomain"="m|mobile"})
     */
    public function mobileHome(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $this->cronSimulation($sortieRepository, $etatRepository,$entityManager);
        //todo faire un base twig spécifique pour le telephone sans header et footer, juste le logo dans le footer
        //todo faire une redirection sur toute les route inaccessible en version mobile
        //todo : requete pour récup toute mes sorti -> nom sorti date de la sorti et lieu, aucun bouton de dispo

        return $this->render('mobile/main/home.html.twig', [

        ]);
    }


    /**
     * Affichage toutes les sorties (sauf celles à l'état 'Passée')
     * Prise en compte du formulaire de recherche
     * @Route("/", name="home")
     */
    public function home(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $this->cronSimulation($sortieRepository, $etatRepository,$entityManager);
        dump($request->getHost());
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
