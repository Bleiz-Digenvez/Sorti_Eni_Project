<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
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
    public function profil(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $participant = $this->getUser();

        $formParticipant = $this->createForm(ParticipantType::class,$participant);

        $formParticipant->handleRequest($request);

        if($formParticipant->isSubmitted()
            && $formParticipant->isValid()){
            if($formParticipant->get('newPassword')->getData()){
                $participant->setMotPasse(
                    $userPasswordHasher->hashPassword(
                        $participant,
                        $formParticipant->get('newPassword')->getData()
                    )
                );

            }
            $manager->persist($participant);
            $manager->flush();
            $this->addFlash('success','Compte Modifier !');
        }

        return $this->render('participant/profil.html.twig', [
            'formParticipant' => $formParticipant->createView()
        ]);
    }
}
