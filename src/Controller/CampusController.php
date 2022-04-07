<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\RechercheCampusType;
use App\Model\RechercheCampus;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/campus", name="campus_")
 */
class CampusController extends AbstractController
{
    /**
     * Fonction qui permet plusieurs actions sur les campus :
     * Affichage d'une liste de tout les campus.
     * Formulaire de recherche de campus.
     * Formulaire de campus qui permet a la fois la modification, ou la création de nouveaux campus
     * @Route("/", name="liste", host="sortir.com")
     */
    public function liste(CampusRepository $campusRepository, Request $request, EntityManagerInterface $em): Response
    {
        //Recuperation de tout les campus
        $campusListe = $campusRepository->findAll();
        //Début du formulaire de création et modification de campus
        $campus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);

        //Traitement du formulaire campus
        //Selon le bouton de soumission clicker on va soit créer, soit modifier un campus
        //Traitement dans le cas d'ajout d'un nouveau campus
        if ($campusForm->get('Ajouter')->isClicked() && $campusForm->isValid()){
                try {

                    //Envoi de l'objet campus en base de donnée
                    $em->persist($campus);
                    $em->flush();
                    //Ajout du message flash et redirection
                    $this->addFlash('success', "Le campus ".$campus->getNom()." à bien été ajoutée");
                    return $this->redirectToRoute('campus_liste');
                } catch (\Exception $ex){
                    if ($ex->getCode() == 1062){
                        //Erreur d'unicité, on renvoi un flash qui indique l'erreur avec des termes simple à l'utilisateur
                        $this->addFlash('danger', "Impossible d'ajouter le campus ".$campus->getNom()." car il existe deja");
                        return $this->redirectToRoute('campus_liste');
                    }
                }
        } else if ($campusForm->get('Modifier')->isClicked() && $campusForm->isValid()){
            //Traitement dans le cas d'une modification de campus
            //On recupere le campus à modifier, et on lui donne sa nouvelle valeur
            $idForm = $campusForm->get('id')->getData();
            $campusUpdate = $campusRepository->find($idForm);
            $campusUpdate->setNom($campusForm->get('nom')->getData());
            try {
                //Envoi de l'objet campus en base de donnée
                $em->persist($campusUpdate);
                $em->flush();
                //Ajout du message flash et redirection
                $this->addFlash('success', 'Le Campus '.$campusUpdate->getNom().' est mise à jour');
                return $this->redirectToRoute('campus_liste');
            } catch (\Exception $ex){
                //Erreur d'unicité, on renvoi un flash qui indique l'erreur avec des termes simple à l'utilisateur
                if ($ex->getCode() == 1062){
                    $this->addFlash('danger', "Impossible de modifier le nom du campus ".$campus->getNom()." car il existe deja");
                    return $this->redirectToRoute('campus_liste');
                }
            }
        } else if ($campusForm->isSubmitted() && !$campusForm->isValid()){
            $this->addFlash('danger', "Les valeurs renseignées ne sont pas valide");
        }

        //Formulaire de recherche de campus
        $recherche = new RechercheCampus();
        $rechercheForm = $this->createForm(RechercheCampusType::class, $recherche);
        $rechercheForm->handleRequest($request);
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            //Dans le cas d'une recherche on modifie la liste des campus pour n'afficher que ceux demandé
            $campusListe = $campusRepository->rechercheCampus($recherche->getNom());
        }

        return $this->render('campus/index.html.twig', [
            'campusListe' => $campusListe,
            'campusForm' => $campusForm->createView(),
            'formRecherche' => $rechercheForm->createView()
        ]);
    }

    /**
     * Fonction qui permet la suppresion des campus
     * @Route("/supprimer/{id}", name="supprimer", host="sortir.com")
     */
    public function supprimer(Campus $campus, EntityManagerInterface $entityManager)
    {
        try {
            //Envoi de l'objet à supprimer en base de données
            $entityManager->remove($campus);
            $entityManager->flush();
            $this->addFlash('success', 'Le campus '.$campus->getNom().' à bien été supprimée');
        } catch (\Exception $ex){
            //Si des sorties ou des participants sont associées à ce campus, on retourne un flash qui indique l'erreur avec des termes simple à l'utilisateur
            $this->addFlash("danger", "Impossible de supprimer le campus ".$campus->getNom()." car elle est associée à des participant ou des sorties");
        }


        return $this->redirectToRoute('campus_liste');
    }
}
