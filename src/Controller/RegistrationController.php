<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/admin/register/form", name="registration_registerForm")
     */
    public function registerForm(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Participant();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setMotPasse(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // TODO: Formatage champs text !

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash('success', 'Le compte '.strtoupper($user->getNom()).' '.$user->getPrenom().' à bien été créer');
            return $this->redirectToRoute('registration_registerForm');
        }

        return $this->render('registration/registerForm.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    /**
     * @Route("/admin/register/csv", name="registration_registerCSV")
     */
    public function registerCSV(UserPasswordHasherInterface $userPasswordHasher,ParticipantRepository $participantRepository, CampusRepository $campusRepository, ValidatorInterface $validator){
        $reader = Reader::createFromPath('../data/participant.csv');
        $results = $reader->fetchAssoc();
        $rowCount=1;
        $userAllowedCount = 0;
        $logs = null;
        foreach($results as $row){
            $user = new Participant();
            $user->setPseudo($row['pseudo']);
            $user->setNom($row['nom']);
            $user->setPrenom($row['prenom']);
            $row['telephone']=='null'?$user->setTelephone(null):$user->setTelephone($row['telephone']);
            $user->setMail($row['mail']);
            $user->setAdministrateur(filter_var($row['administrateur'],FILTER_VALIDATE_BOOLEAN));
            $user->setActif(filter_var($row['actif'],FILTER_VALIDATE_BOOLEAN));
            $user->setMotPasse(
                $userPasswordHasher->hashPassword(
                    $user,
                    'Pa$$w0rd'
                )
            );
            $campus = $campusRepository->findOneBy(['nom' => $row['campus']]);
            if($campus){
                $user->setCampus($campus);
            }

            $error = $validator->validate($user);
            if(count($error) > 0){
                $logsRow[] = $rowCount;
                $logsRow[] = $error;
                $logs[] = $logsRow;
                unset($logsRow);
            }else{
                dump('Enregistrer user');
                $userAllowedCount++;
            }

            $rowCount++;
        }
        dump($logs);

        return $this->render('registration/registerCSV.html.twig', [
            'logs' => $logs,
            'nbUserMax' => $rowCount-1,     
            'nbUserInscrit' => $userAllowedCount
        ]);
    }
}
