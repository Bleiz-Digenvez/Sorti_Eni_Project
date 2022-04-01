<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

            $this->addFlash('success', "La sortie ".$sortie->getNom()." à bien été ajoutée");
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
    public function inscription(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $sortie = null;
        try {
            $sortie = $sortieRepository->inscriptionFind($id, $this->getUser());
        } catch (NoResultException | NonUniqueResultException $e) {
            $this->addFlash('error','Inscription refusée');
        }
        if($sortie){
            $sortie->addParticipant($this->getUser());
            //TODO count sur participants
            if(sizeof($sortie->getParticipants()->getValues()) == $sortie->getNbInscriptionsMax()){
                //TODO find sur le libelle
                $etatCloture = $etatRepository->find(3);
                $sortie->setEtat($etatCloture);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success','Inscription validée à la sortie '.$sortie->getNom());
        }
        return $this->redirectToRoute('main_home');
    }
    /**
     * @Route("/home/sorti/desister/{id}", name="sortie_desister")
     */
    public function desister(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
            EtatRepository $etatRepository)
    {
        $etatCloture = $etatRepository->find(3);
        $sortie = null;
        try {
            $sortie = $sortieRepository->desisterFind($id, $this->getUser());
        } catch (NoResultException | NonUniqueResultException $e) {
            $this->addFlash('error','Désinscription refusée');
        }
        if($sortie) {
            $sortie->removeParticipant($this->getUser());
            if ($sortie->getDateLimiteInscription() > new \DateTime() && $sortie->getEtat() === $etatCloture) {
                //TODO find sur le libelle
                $etatOuvert = $etatRepository->find(2);
                $sortie->setEtat($etatOuvert);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Désinscription validée pour la sortie '. $sortie->getNom());
        }
        return $this->redirectToRoute('main_home');

    }
    /**
     * @Route("/home/sorti/annuler/{id}", name="sortie_annuler")
     */
    public function annuler(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        Request $request
    )
    {
        $sortie = null;
        try {
            $sortie = $sortieRepository->annulerFind($id, $this->getUser());
        } catch (NoResultException | NonUniqueResultException $e) {
            $this->addFlash('error',"Impossible d'annuler cette sortie pour le moment");
        }

        $annulerForm = $this->createForm(AnnulationType::class);

        $annulerForm->handleRequest($request);

        if ($annulerForm->get('Annuler')->isClicked()){
            return $this->redirectToRoute('main_home');
        }

        if($annulerForm->isSubmitted() && $annulerForm->isValid()){
            $sortie->setInfosSortie($annulerForm->get('motif')->getData());

            $etat = $etatRepository->find(6);
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success','La sortie '.$sortie->getNom().' est bien annulée.');

            return $this->redirectToRoute('main_home');

        }
        return $this->render('sortie/annuler.html.twig', [
            'annulerForm' => $annulerForm->createView(),
            'sortie' => $sortie
        ]);

    }

    /**
     * @Route("/home/sorti/detail{id}", name="sortie_detail")
     */
    public function detail(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException(("Cette sortie n'existe pas ! "));
        }

        return $this->render('sortie/detail.html.twig', [
            "sortie" => $sortie
        ]);
    }


//    /**
//     * @Route("/home/sorti/modifier/{id}", name="sortie_modifier")
//     */
//    public function modifier(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
//    {
//        $sortie = $sortieRepository->modifierFind($id, $this->getUser());
//        if(!$sortie){
//            throw new NotFoundHttpException();
//        }
//        $sortie = $sortie[0];
//
//        $sortieForm = $this->createForm(SortieType::class,$sortie);
//
//
//        return $this->render("sortie/modifier.html.twig",[
//            'sortieForm' => $sortieForm->createView(),
//        ]);
//    }



}
