<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lieu", name="lieu_")
 */
class LieuController extends AbstractController
{
    /**
     * @Route("/liste", name="nouveau")
     */
    public function nouveau(LieuRepository $lieuRepository, Request $request, EntityManagerInterface $em): Response
    {
        $lieu = new Lieu();
        $formLieu = $this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);
        //todo: Faire la validation des form lieu et ville, form_error sur les villes
        if ($formLieu->isSubmitted() && $formLieu->isValid()){
            $em->persist($lieu);
            $em->flush();
            $this->addFlash("success", $lieu->getNom() . " a bien été ajouté aux lieux");
            return $this->redirectToRoute('sortie_creation');
        }
        return $this->render('lieu/nouveau.html.twig', [
            'formLieu' => $formLieu->createView()
        ]);
    }
}
