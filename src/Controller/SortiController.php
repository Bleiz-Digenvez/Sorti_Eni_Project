<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SortiController extends AbstractController
{


    /**
     * Fonction qui permet la création de nouvelles sorties, accessible à tous les utilisateurs connectés
     * @Route("/sorti/create", name="sortie_creation", host="sortir.com")
     */
    public function create(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        //Récuperation des état créee et ouvert
        $etatCreee = $etatRepository->find(1);
        $etatOuvert = $etatRepository->find(2);

        //Récuperation de l'utilisateur courant
        $user = $this->getUser();
        $sortie = new Sortie();

        //Passage de l'organisateur et du campus à la sortie
        $sortie->setOrganisateur($user);
        $sortie->setCampus($user->getCampus());

        //Création du formualire
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        //On attribue l'état de la sortie selon le bouton de soumission
        if ($sortieForm->get('Enregistrer')->isClicked()){
            $sortie->setEtat($etatCreee);
        } else if ($sortieForm->get('Publier')->isClicked()){
            $sortie->setEtat($etatOuvert);
        }

        //Soumission du formualire, envoi de la sortie en base de donnée
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();
            //Création du message flash et redirection vers la page d'accueil
            $this->addFlash('success', "La sortie ".$sortie->getNom()." à bien été ajoutée");
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/lieu/recherche", name="sortie_rechercheLieu", host="sortir.com")
     */
    public function rechercheLieu(Request $request, LieuRepository $lieuRepository): Response
    {
        $option = $request->query->get('option');
        if ($option == null){
            return $this->render('sortie/ajax_erreur.html.twig');
        }
        $resultat = $lieuRepository->lieuxParVille($option);

        return $this->render("sortie/ajax_lieu.html.twig", [
            "lieux" => $resultat
        ]);
    }

    /**
     * @Route("/lieu/recherche/info", name="sortie_infoRecherche", host="sortir.com")
     */
    public function affichageInfoLieu(Request $request, LieuRepository $lieuRepository)
    {
        $option = $request->query->get('option');
        if ($option == null){
            return $this->render('sortie/ajax_erreur.html.twig');
        }
        $resultat = $lieuRepository->find($option);

        return $this->render("sortie/ajax_info.html.twig", [
            "lieu" => $resultat
        ]);
    }

    /**
     * @Route("/sorti/inscription/{id}", name="sortie_inscription", host="sortir.com")
     */
    public function inscription(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException(("Cette sortie n'existe pas ! "));
        }

        try{
            $this->denyAccessUnlessGranted('sortie_inscription_voter', $sortie);
            $sortie->addParticipant($this->getUser());
            if(count($sortie->getParticipants()->getValues()) == $sortie->getNbInscriptionsMax()){
                $etatCloture = $etatRepository->findOneBy(['libelle' =>'Clôturée']);
                $sortie->setEtat($etatCloture);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success','Inscription validée à la sortie '.$sortie->getNom());

        }catch (AccessDeniedException $e){
            $this->addFlash('danger','Vous ne pouvez pas vous inscrire a cette activité');
        }


        return $this->redirectToRoute('main_home');
    }

    /**
     * @Route("/sorti/publier/{id}", name="sortie_publier", host="sortir.com")
     */
    public function publier(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException(("Cette sortie n'existe pas ! "));
        }

        try{
            $this->denyAccessUnlessGranted('sortie_publier_voter', $sortie);
            $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $sortie->setEtat($etatOuverte);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success','Publication de '.$sortie->getNom().' validée');
        }catch (AccessDeniedException $e){
            $this->addFlash('danger','Vous ne pouvez pas vous publier cette activité');
        }

        return $this->redirectToRoute('main_home');
    }

    /**
     * @Route("/sorti/desister/{id}", name="sortie_desister", host="sortir.com")
     */
    public function desister(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
            EtatRepository $etatRepository)
    {
        $etatCloture = $etatRepository->findOneBy(['libelle' =>'Clôturée']);
        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException(("Cette sortie n'existe pas ! "));
        }

        try{
            $this->denyAccessUnlessGranted('sortie_desister_voter', $sortie);

            $sortie->removeParticipant($this->getUser());

            if ($sortie->getDateLimiteInscription() > new \DateTime() && $sortie->getEtat() === $etatCloture) {
                $etatOuvert = $etatRepository->findOneBy(['libelle'=>'Ouverte']);
                $sortie->setEtat($etatOuvert);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('warning','Désinscription validée à la sortie '.$sortie->getNom());

        }catch (AccessDeniedException $e){
            $this->addFlash('danger','Vous ne pouvez pas vous désinscrire');
        }
        return $this->redirectToRoute('main_home');

    }
    /**
     * @Route("/sorti/annuler/{id}", name="sortie_annuler", host="sortir.com")
     */
    public function annuler(
        int $id,
        SortieRepository $sortieRepository,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        Request $request
    )
    {

        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException(("Cette sortie n'existe pas ! "));
        }

        try{
            $this->denyAccessUnlessGranted('sortie_annuler_voter', $sortie);
        }catch (AccessDeniedException $e){
            $this->addFlash('danger','Impossible d\'annuler cette sortie pour le moment');
            return $this->redirectToRoute('main_home');
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

            $this->addFlash('warning','La sortie '.$sortie->getNom().' est bien annulée.');

            return $this->redirectToRoute('main_home');

        }
        return $this->render('sortie/annuler.html.twig', [
            'annulerForm' => $annulerForm->createView(),
            'sortie' => $sortie
        ]);
    }
    /**
     * @Route("/sorti/detail/{id}", name="sortie_mobile_detail", host="{subdomain}.sortir.com", defaults={"subdomain"="m"}, requirements={"subdomain"="m|mobile"})
     */
    public function mobileDetail(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            throw $this->createNotFoundException(("Cette sortie n'existe pas ! "));
        }

        return $this->render('mobile/sortie/detail.html.twig', [
            "sortie" => $sortie
        ]);
    }

    /**
     * @Route("/sorti/detail/{id}", name="sortie_detail")
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
