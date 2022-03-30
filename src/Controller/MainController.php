<?php

namespace App\Controller;

use App\Form\RechercheSortieType;
use App\Model\RechercheSortie;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/home", name="main_")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home( Request $request, SortieRepository $sortieRepository): Response
    {
        $recherche= new RechercheSortie();
        $recherche->setParticipant($this->getUser());
        $rechercheSortieForm=$this->createForm(RechercheSortieType::class,$recherche);
        $rechercheSortieForm->handleRequest($request);
        $resultats=$sortieRepository->listSortiesAvecRecherche($recherche);

        if ($rechercheSortieForm->isSubmitted()) {

            $resultats=$sortieRepository->listSortiesAvecRecherche($recherche);
            //dd($resultats);
        }
        return $this->render('main/home.html.twig', [
            'rechercheSortieForm' => $rechercheSortieForm->createView(),
            'resultats'=>$resultats,
        ]);
    }
}
