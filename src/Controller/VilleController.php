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
     * @Route("/liste", name="liste", host="sortir.com")
     */
    public function liste(VilleRepository $villeRepository, Request $request, EntityManagerInterface $entityManager, MobileDetector $mobileDetector): Response
    {
        if($mobileDetector->isMobile()){
            throw $this->createNotFoundException('Page non accessible en Nique ta Mere');
        }

        //Récuperation de toutes les villes
        $listeVille = $villeRepository->findAll();

        //Début du formulaire d'ajout de ville
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);

        //Traitement si c'est un ajout d'une nouvelles ville
        if ($villeForm->get('Ajouter')->isClicked()){
            if ($villeForm->isSubmitted() && $villeForm->isValid()){
                $entityManager->persist($ville);
                $entityManager->flush();
                //Ajout du message flash
                $this->addFlash('success', "La ville ".$ville->getNom()." à bien été ajoutée");
                return $this->redirectToRoute('ville_liste');
            }
        }else if ($villeForm->get('Modifier')->isClicked()){
            //Traitement si c'est une mise a jour d'une ville
            //Récuperation de l'objet
            $idForm = $villeForm->get('id')->getData();
            $villeAUpdate = $villeRepository->find($idForm);
            //Mise a jour des valeur
            $villeAUpdate->setNom($villeForm->get('nom')->getData());
            $villeAUpdate->setCodePostal($villeForm->get('codePostal')->getData());
            //Traitement
            $entityManager->persist($villeAUpdate);
            $entityManager->flush();
            $this->addFlash('success', 'La ville '.$villeAUpdate->getNom().' est mise à jour');
            return $this->redirectToRoute('ville_liste');
        }
        //Fin du traitement formulaire d'ajout de ville
        //Début du formulaire de recherche de ville
        $recherche = new RechercheVille();
        $rechercheForm = $this->createForm(RechercheVilleType::class, $recherche);
        $rechercheForm->handleRequest($request);
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            $listeVille = $villeRepository->rechercheVille($recherche->getNom());
            dump($listeVille);
        }

        return $this->render('ville/liste.html.twig', [
            'villes' => $listeVille,
            'formVille' => $villeForm->createView(),
            'formRecherche' => $rechercheForm->createView()
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="supprimer", host="sortir.com")
     */
    public function supprimer(Ville $ville ,EntityManagerInterface $entityManager)
    {
        try {
            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('success', 'La ville '.$ville->getNom().' à bien été supprimée');
        } catch (\Exception $ex){
            $this->addFlash("danger", "Impossible de supprimer la ville ".$ville->getNom()." car elle est associée à des sorties à venir");
        }


        return $this->redirectToRoute('ville_liste');
    }

}
