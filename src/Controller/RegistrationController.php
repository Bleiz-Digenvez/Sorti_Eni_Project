<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationCSVType;
use App\Form\RegistrationFormType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    //Lien pour l'ajout des utilisateur via Formulaire
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
    //Lien pour l'ajout des utilisateur via CSV
    /**
     * @Route("/admin/register/csv", name="registration_registerCSV")
     */
    public function registerCSV(
        UserPasswordHasherInterface $userPasswordHasher,
        ParticipantRepository $participantRepository,
        CampusRepository $campusRepository,
        ValidatorInterface $validator,
        Request $request,
        EntityManagerInterface $entityManager
    ){

        //creation du form servant a upload le fichier CSV

        $registrationCSVForm = $this->createForm(RegistrationCSVType::class);
        $registrationCSVForm->handleRequest($request);

        if($registrationCSVForm->isSubmitted() && $registrationCSVForm->isValid()){
            $file = $registrationCSVForm['csv']->getData();
            if($file->getClientOriginalExtension() !== 'csv'){
                $this->addFlash('danger','Mauvaise extension de fichier.');
            }else{
                //création de l'environnement de l'administrateur
                $path = '../data/'.$this->getUser()->getUserIdentifier();
                $filesystem = new Filesystem();
                $this->createAdminDirectory($path, $filesystem);
                $logFile = $path.'/ParticipantNonAjouter.csv';

                if(!$file == null){
                    $file->move($path,'AjoutParticipant.csv');
                    $filePath = $path.'/AjoutParticipant.csv';
                    $array = $this->readCSVFile($filePath,$userPasswordHasher,$campusRepository,$validator,$filesystem,$logFile, $entityManager);
                    return $this->render('registration/registerCSV.html.twig', [
                        'form' => $registrationCSVForm->createView(),
                    ]);
                }
            }
        }
        return $this->render('registration/registerCSV.html.twig', [
            'form' => $registrationCSVForm->createView(),
        ]);
    }
    //Lien de téléchargement du fichier de log des utilisateur non enregistrer
    /**
     * @Route("/admin/register/csv/log", name="registration_registerCSVLog")
     */
    public function registerCSVLOG(){
        $path = '../data/'.$this->getUser()->getUserIdentifier().'/ParticipantNonAjouter.csv';
        $filesystem = new Filesystem();
        if($filesystem->exists($path)){
            $file = new File($path);
            return $this->file($file);
        }else{
            $this->addFlash('danger','Aucun fichier de log trouver');
            return $this->$this->redirectToRoute('registration_registerCSV');
        }
    }
    //Lien permetant le téléchargement du template du CSV
    /**
     * @Route("/admin/register/csv/sample", name="registration_registerCSVLogSample")
     */
    public function registerCSVLOGSample(){
        $path = '../data/CSVSample.csv';
        $filesystem = new Filesystem();
        if($filesystem->exists($path)){
            $file = new File($path);
            return $this->file($file);
        }else{
            $this->addFlash('danger','Aucun fichier de log trouver');
            return $this->$this->redirectToRoute('registration_registerCSV');
        }
    }

    //Création de l'environnement de l'admin ajoutant les utilisateur via CSV
    private function createAdminDirectory(string $path, Filesystem $filesystem): void{
        $filesystem->mkdir($path);
        $path = $path.'/ParticipantNonAjouter.csv';
        $filesystem->dumpFile($path,"pseudo,nom,prenom,telephone,mail,administrateur,actif,campus\n");

    }


    //Fonction lisant le CSV et enregistrant les utilisateurs dans la BDD
    private function readCSVFile(string $filePath,
                                 UserPasswordHasherInterface $userPasswordHasher,
                                 CampusRepository $campusRepository,
                                 ValidatorInterface $validator,
                                 Filesystem $filesystem,
                                 string $logFile,
                                 EntityManagerInterface $entityManager
    ): array
    {

        //Variable pour la lecture du fichier CSV
        $reader = Reader::createFromPath($filePath);
        //Utilise la premier ligne du CSV pour cree un tableau associatif
        $results = $reader->fetchAssoc();
        $rowCount=1;
        $userAllowedCount = 0;

        //Lecture ligne par ligne du CSV
        foreach($results as $row){
            //Création de l'utilisateur avec les champs contenue dans la ligne lue
            $user = new Participant();
            $user->setPseudo($row['pseudo']);
            $user->setNom($row['nom']);
            $user->setPrenom($row['prenom']);
            $row['telephone']=='null'?$user->setTelephone(null):$user->setTelephone($row['telephone']);
            $user->setMail($row['mail']);
            $user->setAdministrateur(filter_var($row['administrateur'],FILTER_VALIDATE_BOOLEAN));
            $user->setActif(filter_var($row['actif'],FILTER_VALIDATE_BOOLEAN));
            //Utilisation d'un mot de passe pars default
            $user->setMotPasse(
                $userPasswordHasher->hashPassword(
                    $user,
                    'Pa$$w0rd'
                )
            );
            //Recherche du campus nomme dans la BDD
            $campus = $campusRepository->findOneBy(['nom' => $row['campus']]);
            if($campus){
                $user->setCampus($campus);
            }
            //Control de toutes les contrainte ( uniciter/ non nullable/ REGEX/ taille) instaurer dans la class Participant via ASSERT
            $error = $validator->validate($user);
            //Si une erreur est lever alors on ecris dans le fichier Participant Non Ajouter le caracteristique du participant + les raisons du refus
            if($error->count() > 0){
                dump($error);
                $fileContent = $user->getPseudo().
                    ','.$user->getNom().
                    ','.$user->getPrenom().
                    ','.$user->getTelephone().
                    ','.$user->getMail().
                    ','.$user->getAdministrateur().
                    ','.$user->getActif().
                    ','.($user->getCampus()!= null?$user->getCampus()->getNom():$row['campus']).
                    ',Cause ! ';
                //Ecriture des raison du refus a la fin de la ligne
                foreach($error as $e){
                    $fileContent = $fileContent.strtoupper($e->getPropertyPath()).': '.$e->getMessage();
                    if($e->getPropertyPath() == 'campus'){
                        $fileContent = $fileContent.$row['campus'];
                    }
                }
                $fileContent = $fileContent."\n";
                //Ajout au fichier /data/adminID/PariticipantNonAjouter.CSV les données
                $filesystem->appendToFile($logFile,$fileContent);
                $filesystem->touch($logFile);
            }else{
                // si il n'y as pas d'erreur alors on ajoute l'utilisateur dans la BDD
                $entityManager->persist($user);
                $entityManager->flush();
                $userAllowedCount++;
            }
            $rowCount++;
        }

        if($rowCount -1 == $userAllowedCount){
            $this->addFlash('success','Tous les utilisateur on été ajouter!');
        }elseif ($userAllowedCount == 0){
            $this->addFlash('danger','Aucun utilisateur ajouter sur '.($rowCount-1));
        }else{
            $this->addFlash('warning',$userAllowedCount.' Utilisateur on été ajouter sur '.($rowCount-1).' utilisateur au total');
        }

        return array(
            'nbUserMax' => $rowCount -1,
            'nbUserInscrit' => $userAllowedCount
        );
    }
}
