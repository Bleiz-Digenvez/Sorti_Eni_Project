<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortiController extends AbstractController
{
    /**
     * @Route("/home/sorti/create", name="sortie_creation")
     */
    public function create(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        //Création de l'état par defaut
        $etatCreee = $etatRepository->find(1);
        $etatOuvert = $etatRepository->find(2);
        //Récuperation de l'utilisateur
        $user = $this->getUser();
        $sortie = new Sortie();
        $sortie->setOrganisateur($user);
        $sortie->setCampus($user->getCampus());
        //todo: Gérer les états
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        if ($sortieForm->get('Enregistrer')->isClicked()){
            $sortie->setEtat($etatCreee);
        } else if ($sortieForm->get('Publier')->isClicked()){
            $sortie->setEtat($etatOuvert);
        }

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', "La sortie à bien été ajoutée");
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/lieu/recherche", name="sortie_rechercheLieu")
     */
    public function rechercheLieu(Request $request, LieuRepository $lieuRepository): Response
    {
        $option = $request->query->get('option');
        if ($option == null){
            return $this->redirectToRoute('main_home');
        }
        $resultat = $lieuRepository->lieuxParVille($option);

        return $this->render("sortie/ajax_lieu.html.twig", [
            "lieux" => $resultat
        ]);
    }

    /**
     * @Route("/lieu/recherche/info", name="sortie_infoRecherche")
     */
    public function affichageInfoLieu(Request $request, LieuRepository $lieuRepository)
    {
        $option = $request->query->get('option');
        if ($option == null){
            return $this->redirectToRoute('main_home');
        }
        $resultat = $lieuRepository->find($option);

        return $this->render("sortie/ajax_info.html.twig", [
            "lieu" => $resultat
        ]);
    }

    /**
     * @Route("/home/sorti/inscription/{id}", name="sortie_inscription")
     */
    public function inscription(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {
        $sortie = $sortieRepository->find($id);
        if(!$sortie){
            throw $this->createNotFoundException('Sortie n\'existe pas');
        }
        if($sortie->getDateLimiteInscription() > new \DateTime()
            && !$sortie->getParticipants()->contains($this->getUser())
            && $sortie->getEtat()->getId() == 2
        ){
            $sortie->addParticipant($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success','Inscription validée');
        }else{
            $this->addFlash('error','Inscription refuser');
        }
        return $this->redirectToRoute('main_home');
    }



}
