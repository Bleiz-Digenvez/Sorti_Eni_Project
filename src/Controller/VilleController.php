<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\RechercheVilleType;
use App\Form\VilleType;
use App\Model\RechercheVille;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ville", name="ville_")
 */
class VilleController extends AbstractController
{
    /**
     * Fonction qui permet à la fois :
     * d'afficher une liste de toutes les villes
     * de faire une recherche sur cette liste
     * d'utiliser un formulaire qui permet d'ajouter ou de modifier des villes
     * @Route("/liste", name="liste", host="sortir.com")
     */
    public function liste(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager, MobileDetector $mobileDetector): Response
    {
        //Recuperation de toutes les villes
        $listeVille = $villeRepository->findAll();
        //Début du formulaire d'ajout/modification de ville
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        //Traitement du formulaire ville
        //Selon le bouton de soumission clicker on va soit créer, soit modifier une ville
        //Traitement dans le cas d'ajout d'une nouvelle ville
        if ($villeForm->get('Ajouter')->isClicked()){
            if ($villeForm->isSubmitted() && $villeForm->isValid()){
                try {
                    //Envoi de l'objet ville en base de données
                    $entityManager->persist($ville);
                    $entityManager->flush();
                    //Ajout du message flash et redirection
                    $this->addFlash('success', "La ville ".$ville->getNom()." à bien été ajoutée");
                    return $this->redirectToRoute('ville_liste');
                } catch (\Exception $ex){
                    //Erreur d'unicité, on renvoi un flash qui indique l'erreur avec des termes simple à l'utilisateur
                    if ($ex->getCode() == 1062){
                        $this->addFlash("danger", "Impossible d'ajouter la ville ".$ville->getNom()." car elle existe deja");
                        return $this->redirectToRoute('ville_liste');
                    }
                }
            }
        }else if ($villeForm->get('Modifier')->isClicked()){
            //Traitement dans le cas d'une modification de ville
            try {
                //Recuperation de l'objet ville à modifier
                $idForm = $villeForm->get('id')->getData();
                $villeAUpdate = $villeRepository->find($idForm);

                //Mise à jour avec les nouvelles valeurs
                $villeAUpdate->setNom($villeForm->get('nom')->getData());
                $villeAUpdate->setCodePostal($villeForm->get('codePostal')->getData());

                //Envoi de l'objet ville en base de données
                $entityManager->persist($villeAUpdate);
                $entityManager->flush();
                //Ajout du message flash et redirection
                $this->addFlash('success', 'La ville '.$villeAUpdate->getNom().' est mise à jour');
                return $this->redirectToRoute('ville_liste');
            } catch (\Exception $ex) {
                //Erreur d'unicité, on renvoi un flash qui indique l'erreur avec des termes simple à l'utilisateur
                if ($ex->getCode() == 1062){
                    $this->addFlash("danger", "Impossible d'ajouter la ville ".$ville->getNom()." car elle existe deja");
                    return $this->redirectToRoute('ville_liste');
                }
            }
        }

        //Début du formulaire de recherche de ville
        $recherche = new RechercheVille();
        $rechercheForm = $this->createForm(RechercheVilleType::class, $recherche);
        $rechercheForm->handleRequest($request);
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            //Dans le cas d'une recherche on modifie la liste des villes pour n'afficher que celle demandé
            $listeVille = $villeRepository->rechercheVille($recherche->getNom());
        }

        return $this->render('ville/liste.html.twig', [
            'villes' => $listeVille,
            'formVille' => $villeForm->createView(),
            'formRecherche' => $rechercheForm->createView()
        ]);
    }

    /**
     * Fonction qui permet la suppresion d'une ville
     * @Route("/supprimer/{id}", name="supprimer", host="sortir.com")
     */
    public function supprimer(Ville $ville ,EntityManagerInterface $entityManager)
    {
        try {
            //Envoi de l'objet à supprimer en base de données
            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('warning', 'La ville '.$ville->getNom().' à bien été supprimée');
        } catch (\Exception $ex){
            //Si des sorties sont associées à cette ville, on retourne un flash qui indique l'erreur avec des termes simple à l'utilisateur
            $this->addFlash("danger", "Impossible de supprimer la ville ".$ville->getNom()." car elle est associée à des sorties à venir");
        }


        return $this->redirectToRoute('ville_liste');
    }

}
