<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/profil", name="participant_")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("", name="profil")
     */
    public function profil(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $participant = $this->getUser();

        $oldPseudo = $participant->getPseudo();

        $filesystem = new Filesystem();


        //Récuperation de l'image de profil de l'utilisateur, ou de l'image par defaut
        $chemin = 'img/participant/utilisateur' . "-" . $participant->getId() . '.jpg';
        if (!$filesystem->exists($chemin)){
            $chemin = 'img/PlaceHolderPicture.jpg';
        }
        //Création du formulaire de modification
        $formParticipant = $this->createForm(ParticipantType::class,$participant);
        $formParticipant->handleRequest($request);

        if($formParticipant->isSubmitted() && $formParticipant->isValid()){
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
            if($participant->getPseudo() != $oldPseudo){
                $path = '../data/';
                if($filesystem->exists($path.$oldPseudo)){
                    $filesystem->rename($path.$oldPseudo,$path.$participant->getPseudo());
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
     * @Route("/detail/{id}", name="detail")
     */
    public function detail(int $id, ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->find($id);
        //Redirige si le participant demandé est l'utilisateur courant
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
     * @Route("/liste/", name="liste")
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
     * @Route("/admin/desactiver/", name="desactiver")
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
     * @Route("/admin/supprimer/", name="supprimer")
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

}
