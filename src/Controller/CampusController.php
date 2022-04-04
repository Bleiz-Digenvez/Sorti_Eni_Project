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
 * @Route("/home/campus", name="campus_")
 */
class CampusController extends AbstractController
{
    /**
     * @Route("/", name="liste")
     */
    public function liste(CampusRepository $campusRepository, Request $request, EntityManagerInterface $em): Response
    {
        $campusListe = $campusRepository->findAll();
        //Fromulaire de création et modification
        $campus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);
        if ($campusForm->get('Ajouter')->isClicked() && $campusForm->isValid()){
            if ($campusForm->isValid()){
                $em->persist($campus);
                $em->flush();
                $this->addFlash('success', "Le campus ".$campus->getNom()." à bien été ajoutée");
                return $this->redirectToRoute('campus_liste');
            }
        } else if ($campusForm->get('Modifier')->isClicked() && $campusForm->isValid()){
            $idForm = $campusForm->get('id')->getData();
            $campusUpdate = $campusRepository->find($idForm);
            $campusUpdate->setNom($campusForm->get('nom')->getData());
            $em->persist($campusUpdate);
            $em->flush();
            $this->addFlash('success', 'Le Campus '.$campusUpdate->getNom().' est mise à jour');
            return $this->redirectToRoute('campus_liste');
        }

        //Formulaire de recherche de campus
        $recherche = new RechercheCampus();
        $rechercheForm = $this->createForm(RechercheCampusType::class, $recherche);
        $rechercheForm->handleRequest($request);
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            $campusListe = $campusRepository->rechercheCampus($recherche->getNom());
        }

        return $this->render('campus/index.html.twig', [
            'campusListe' => $campusListe,
            'campusForm' => $campusForm->createView(),
            'formRecherche' => $rechercheForm->createView()
        ]);
    }

    /**
     * @Route("/supprimer/{id}", name="supprimer")
     */
    public function supprimer(Campus $campus, EntityManagerInterface $entityManager)
    {
        try {
            $entityManager->remove($campus);
            $entityManager->flush();
            $this->addFlash('success', 'Le campus '.$campus->getNom().' à bien été supprimée');
        } catch (\Exception $ex){
            $this->addFlash("danger", "Impossible de supprimer le campus ".$campus->getNom()." car elle est associée à des participant ou des sorties");
        }


        return $this->redirectToRoute('campus_liste');
    }
}
