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

        $chemin = 'img/participant/'. $participant->getUserIdentifier() . "-" . $participant->getNom() . '.jpg';
        dump($chemin);
        if (!file_exists($chemin)){
            $chemin = 'img/PlaceHolderPicture.jpg';
        }

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
            $extention = 'jpg';
            $nomImage = $participant->getUserIdentifier() . "-" . $participant->getNom() . '.' . $extention;
            $repertoire = 'img/participant';
            $image->move($repertoire, $nomImage);
            //fin de l'update image
            $manager->persist($participant);
            $manager->flush();
            $this->addFlash('success','Compte Modifier !');
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

        $chemin = 'img/participant/'. $participant->getUserIdentifier() . "-" . $participant->getNom() . '.jpg';
        if (!file_exists($chemin)){
            $chemin = '../../../../public/img/PlaceHolderPicture.jpg';
        } else if (file_exists($chemin)){
            $chemin = '../../../../public/img/participant/'. $participant->getUserIdentifier() . "-" . $participant->getNom() . '.jpg';
        }
        return $this->render('participant/detail.html.twig', [
            'participant' => $participant,
            'cheminImg' => $chemin
        ]);
    }
}
