<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/home/profil", name="participant_")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("", name="profil")
     */
    public function profil(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $participant = $this->getUser();
        //Récuperation de l'image de profil de l'utilisateur, ou de l'image par defaut
        $chemin = 'img/participant/utilisateur' . "-" . $participant->getId() . '.jpg';
        if (!file_exists($chemin)){
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
            //fin de l'update image
            $manager->persist($participant);
            $manager->flush();
            $this->addFlash('success','Compte Modifier !');
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
}
