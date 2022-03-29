<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortiController extends AbstractController
{
    /**
     * @Route("/home/sorti/create", name="create_sorti")
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $sortie = new Sortie();
        $sortie->setOrganisateur($user);
        $sortie->setCampus($user->getCampus());
        //todo: Gérer les états
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', "La sortie à bien été ajoutée");
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sorti/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/lieu/recherche", name="app_rechercheLieu")
     */
    public function rechercheLieu(): Response
    {
        return 'nvfinpz';
    }
}
