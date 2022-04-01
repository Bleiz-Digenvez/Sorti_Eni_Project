<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Model\Recherche;
use App\Model\RechercheSortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Sortie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Sortie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function listSortiesAvecRecherche(RechercheSortie $rechercheSortie): array
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p');

        //afficher les sortie passées depuis 1 mois max
        $queryBuilder
            ->andWhere('s.dateHeureDebut > :date')
            ->setParameter('date', ((new \DateTime())->modify('-1 month')) );

        //si la case "Sorties passées" est cochée on affiche QUE les sorties passées
        if ($rechercheSortie->getPassees()) {
            $queryBuilder->andWhere('e.libelle = :etat3')
                ->setParameter('etat3', 'Passée');
        }

        //si un campus est selectionné
        if($rechercheSortie->getSite()) {
            $queryBuilder->join('s.campus', 'c')
                ->addSelect('c')
                ->andWhere('c.nom = :site')
                ->setParameter('site', $rechercheSortie->getSite()->getNom());
        }

        //date Max de la sorties
        if ($rechercheSortie->getDateMax()) {
            $queryBuilder->andWhere('s.dateHeureDebut <= :dateMax');
            $queryBuilder->setParameter('dateMax', $rechercheSortie->getDateMax());
        }
        //date min de la sortie
        if ($rechercheSortie->getDateMin()) {
            $queryBuilder->andWhere('s.dateHeureDebut >= :dateMin');
            $queryBuilder->setParameter('dateMin', $rechercheSortie->getDateMin());
        }
        //rechercher si le nom de la sortie contient
        if ($rechercheSortie->getNomSortie()) {
            $queryBuilder->andWhere('s.nom LIKE :nom');
            $queryBuilder->setParameter('nom', '%'.$rechercheSortie->getNomSortie().'%');
        }
        //n'afficher que les sorties dont le USER est organisateur.
        if ($rechercheSortie->getOrganisateur()) {
            $queryBuilder->andWhere('s.organisateur = :organisateur');
            $queryBuilder->setParameter('organisateur', $rechercheSortie->getParticipant()->getId());
        }

        //si l'utilisateur clique sur inscrit et n'est pas inscrit, on affiche tout.
        if ($rechercheSortie->getInscrit() && $rechercheSortie->getPasInscrit()) {
            //sinon n'afficher que les sorties ou le USER est inscrit
        } else if ($rechercheSortie->getInscrit()) {
            $queryBuilder
                ->andWhere(' :participant MEMBER OF s.participants')
                ->setParameter('participant', $rechercheSortie->getParticipant()->getId());
            //sinon n'afficher que les sorties ou le USER n'est pas inscrit
        } else if ($rechercheSortie->getPasInscrit()) {
            $queryBuilder->andWhere(':participant  NOT MEMBER OF s.participants  ');
            $queryBuilder->setParameter('participant', $rechercheSortie->getParticipant()->getId());
        }

        // select pour récupérer les résultats
        $queryBuilder->addOrderBy('s.dateHeureDebut', 'DESC');
        $query = $queryBuilder->getQuery();
        $sorties = $query->getResult();
        // compte le nombre de sorties trouvées
        $nbSorties = count($sorties);

        return [
            'sorties' => $sorties,
            'nbSorties' => $nbSorties
        ];
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
