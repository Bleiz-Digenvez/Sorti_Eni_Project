<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * Page servant a la modification de son compte
     * @Route("/profil", name="participant_profil",host="sortir.com")
     */
    public function profil(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        //Recupération du compte accuellement connecté
        $participant = $this->getUser();

        //Set up de la variable servant a savoir si le pseudo a été modifier
        $oldPseudo = $participant->getUserIdentifier();

        $filesystem = new Filesystem();

        //Récuperation de l'image de profil de l'utilisateur, ou de l'image par defaut
        $chemin = 'img/participant/utilisateur' . "-" . $participant->getId() . '.jpg';
        if (!$filesystem->exists($chemin)){
            $chemin = 'img/PlaceHolderPicture.jpg';
        }
        //Création du formulaire de modification avec les données utilisateurs dedans
        $formParticipant = $this->createForm(ParticipantType::class,$participant);
        $formParticipant->handleRequest($request);

        if($formParticipant->isSubmitted() && $formParticipant->isValid()){
            //Controlle si les champs mdp on été remplis , si oui on modifie le mdp dans la bases
            if($formParticipant->get('newPassword')->getData()){
                $participant->setMotPasse(
                    $userPasswordHasher->hashPassword(
                        $participant,
                        $formParticipant->get('newPassword')->getData()
                    )
                );

            }
            // Début de l'update image
            $image = $formParticipant['image']->getData();
            if (!$image == null){
                $extention = 'jpg';
                $nomImage = 'utilisateur' . "-" . $participant->getId() . '.' . $extention;
                $repertoire = 'img/participant';
                $image->move($repertoire, $nomImage);
            }
            //Modification du fichier admin au cas ou il y a changement de pseudo de l'utilisateur admin
            if($participant->getUserIdentifier() != $oldPseudo){
                $path = '../data/';
                if($filesystem->exists($path.$oldPseudo)){
                    $filesystem->rename($path.$oldPseudo,$path.$participant->getUserIdentifier());
                }
            }
            //fin de l'update image
            $manager->persist($participant);
            $manager->flush();
            $this->addFlash('success','Votre compte a été modifié !');
            // Redirection pour prendre en compte le changement d'image à l'écran
            return $this->redirect($request->getUri());
        }

        return $this->render('participant/profil.html.twig', [
            'formParticipant' => $formParticipant->createView(),
            'cheminImg' => $chemin
        ]);
    }

    /**
     * @Route("/profil/detail/{id}", name="participant_detail",host="sortir.com")
     */
    public function detail(int $id, ParticipantRepository $participantRepository)
    {
        //Recuperation des informations de l'utilisateur demandé
        $participant = $participantRepository->find($id);
        //Redirection si le participant demandé est l'utilisateur courant
        if ($participant == $this->getUser()){
            return $this->redirectToRoute('participant_profil');
        }
        //Retourne une erreur si l'utilisateur n'existe pas
        if (!$participant){
            throw $this->createNotFoundException('Oh .. il sembre que cette utilisateur n\'existe pas');
        }
        // Récupère la photo de profil ou la photo par defaut utilisateur
        $chemin = 'img/participant/utilisateur'. "-" . $participant->getId() . '.jpg';
        if (!file_exists($chemin)){
            $chemin = 'img/PlaceHolderPicture.jpg';
        }
        return $this->render('participant/detail.html.twig', [
            'participant' => $participant,
            'cheminImg' => $chemin
        ]);
    }

    /**
     * Affiche la iste des tous les participants
     * @Route("/admin/liste/", name="participant_liste",host="sortir.com")
     */
    public function liste(ParticipantRepository $participantRepository)
    {
        //récupere la liste des utilisateurs
        $participants = $participantRepository->findAll();
        //Retourne une erreur si la liste est vide
        if (!$participants){
            throw $this->createNotFoundException('Oh .. il y a un probléme !');
        }
        return $this->render('participant/liste.html.twig', [
            'participants' => $participants,
        ]);
    }

    /**
     * Désactive ou active les participants
     * Selon la liste d'ids et l'état (boolean) passés en paramétres
     * @Route("/admin/desactiver/", name="participant_desactiver",host="sortir.com")
     */
    public function estActive(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        //récupere la liste des ids
        $listeId = $request->query->get('utilisateursSelectionnes');
        $etat = ($request->query->get('etat')) == "true";
        //récupere la liste des utilisateurs
        $listeUtilisateurs = $participantRepository->findBy(array('id' => $listeId));
        //Pour chaque utilisateur, changement de l'état 'actif' en BDD
       foreach ($listeUtilisateurs as $utilisateur) {
            $utilisateur-> setActif($etat);
            $entityManager->persist($utilisateur);
            $entityManager->flush();
        }
        return $this->redirectToRoute('participant_liste');
    }

    /**
     * Supprimer les participants
     * Selon la liste d'ids passés en paramétres
     * @Route("/admin/supprimer/", name="participant_supprimer",host="sortir.com")
     */
    public function supprimer(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        try {
            //récupere la liste des ids
            $listeId = $request->query->get('utilisateursASupprimer');
            //récupere la liste des utilisateurs
            $listeUtilisateurs = $participantRepository->findBy(array('id' => $listeId));
            //Pour chaque utilisateur, changement de l'état 'actif' en BDD
            foreach ($listeUtilisateurs as $utilisateur) {
                if($utilisateur->getSorties()->count()>0 || $utilisateur->getSortiesOrganisees()->count()>0) {
                    throw $this->createNotFoundException('');
                }
                $entityManager->remove($utilisateur);
                $entityManager->flush();
            }
            $this->addFlash('success', 'Validation: '.count($listeUtilisateurs).' utilisateur(s) supprimé(s)');
        } catch(\Exception $exception) {
            $this->addFlash('danger', "Impossible de supprimer le(s) utilisateur(s), le(s) compte(s) sont actif(s) sur des sorties !");
        }
        return $this->redirectToRoute('participant_liste');
    }

    /**
     * Fonction pour requete AJAX
     * Recherche utilisateur selon un champs de saisi sur les attributs pseudo et/ou nom et/ou prenom
     * @Route("/admin/rechercheParNomPrenomPseudo", name="participant_rechercheParNomPrenomPseudo",host="sortir.com")
     */
    public function rechercheParNomPrenomPseudo(ParticipantRepository $participantRepository, Request $request):Response
    {
        $saisi = $request->query->get('saisi');
        $participants = $participantRepository->findByNomPrenomPseudo($saisi);
        return $this->render("participant/ajax_recherche.html.twig", [
            'participants'=>$participants
        ]);
    }

}
