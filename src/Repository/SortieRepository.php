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
            ->leftJoin('s.participants', 'p');

        //si la case "Sorties passées" est cochée on affiche QUE les sorties passées sinon on n'affiche pas les sorties passées
        if (!$rechercheSortie->getPassees()) {
            $queryBuilder->andWhere('e.libelle != :etat2')
                ->setParameter('etat2', 'Passée');
        } else {
            $queryBuilder->andWhere('e.libelle = :etat3')
                ->setParameter('etat3', 'Passée');
        }
        //si un campus est selectionné
        if($rechercheSortie->getSite()) {
            $queryBuilder->join('s.campus', 'c')
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
        $queryBuilder->addOrderBy('s.dateHeureDebut', 'ASC');
        $query = $queryBuilder->getQuery();
        $sorties = $query->getResult();
        // compte le nombre de sorties trouvées
        $nbSorties = count($sorties);

        return [
            'sorties' => $sorties,
            'nbSorties' => $nbSorties
        ];
    }

    /**
     * @param int $id
     * @param UserInterface $user
     * @return Sortie|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function inscriptionFind(int $id, UserInterface $user): ?Sortie
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder
            ->join('s.etat', 'e')
            ->leftJoin('s.participants', 'p')
            ->andWhere('s.nbInscriptionsMax > SIZE(s.participants)')
            ->andWhere(':id = s.id')
            ->andWhere('s.dateLimiteInscription > CURRENT_DATE()')
            ->andWhere(':participant NOT MEMBER OF s.participants')
            ->andWhere('e.libelle = \'Ouverte\'')
            ->setParameter('participant', $user)
            ->setParameter('id', $id);
        $query = $queryBuilder->getQuery();
        return $query->getSingleResult();
    }


//    public function modifierFind(int $id, Participant $participant): ?Sortie
//    {
//        $queryBuilder = $this->createQueryBuilder('s');
//
//        $queryBuilder
//            ->join('s.etat', 'e')
//            ->join('s.organisateur', 'p')
//            ->join('s.lieu','l')
//            ->leftJoin('l.ville','v')
//            ->andWhere(':id = s.id')
//            ->andWhere(':participant = s.organisateur')
//            ->andWhere('e.libelle = \'Créée\'')
//            ->setParameter('participant', $participant)
//            ->setParameter('id', $id);
//        $query = $queryBuilder->getQuery();
//        return $query->getSingleResult();
//    }

    /**
     * @param int $id
     * @param UserInterface $user
     * @return Sortie|null
     * @throws NoResultException
     * @throws NonUniqueResultException
     */

    public function desisterFind(int $id, UserInterface $user): ?Sortie
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder
            ->join('s.etat', 'e')
            ->join('s.participants', 'p')
            ->andWhere(':id = s.id')
            ->andWhere(':participant MEMBER OF s.participants')
            ->andWhere('s.dateHeureDebut > CURRENT_DATE()')
            ->setParameter('participant', $user)
            ->setParameter('id', $id);
        $query = $queryBuilder->getQuery();
        return $query->getSingleResult();
    }

    /**
     * @param int $id
     * @param UserInterface $user
     * @return Sortie|null
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function annulerFind(int $id, UserInterface $user): ?Sortie
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder
            ->join('s.etat', 'e')
            ->andWhere(':id = s.id')
            ->andWhere(':participant = s.organisateur')
            ->andWhere('s.dateHeureDebut > CURRENT_DATE()')
            ->setParameter('participant', $user)
            ->setParameter('id', $id);
        $query = $queryBuilder->getQuery();
        return $query->getSingleResult();
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
