<?php

namespace App\Model;


class RechercheSortie
{
    private $participant;
    private $site;
    private $nomSortie;
    private $dateMin;
    private $dateMax;
    private $organisateur;
    private $inscrit;
    private $pasInscrit;
    private $passees;

    /**
     * @return mixed
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @param mixed $participant
     */
    public function setParticipant($participant): void
    {
        $this->participant = $participant;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site): void
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getNomSortie()
    {
        return $this->nomSortie;
    }

    /**
     * @param mixed $nomSortie
     */
    public function setNomSortie($nomSortie): void
    {
        $this->nomSortie = $nomSortie;
    }

    /**
     * @return mixed
     */
    public function getDateMin()
    {
        return $this->dateMin;
    }

    /**
     * @param mixed $dateMin
     */
    public function setDateMin($dateMin): void
    {
        $this->dateMin = $dateMin;
    }

    /**
     * @return mixed
     */
    public function getDateMax()
    {
        return $this->dateMax;
    }

    /**
     * @param mixed $dateMax
     */
    public function setDateMax($dateMax): void
    {
        $this->dateMax = $dateMax;
    }

    /**
     * @return mixed
     */
    public function getOrganisateur()
    {
        return $this->organisateur;
    }

    /**
     * @param mixed $organisateur
     */
    public function setOrganisateur($organisateur): void
    {
        $this->organisateur = $organisateur;
    }

    /**
     * @return mixed
     */
    public function getInscrit()
    {
        return $this->inscrit;
    }

    /**
     * @param mixed $inscrit
     */
    public function setInscrit($inscrit): void
    {
        $this->inscrit = $inscrit;
    }

    /**
     * @return mixed
     */
    public function getPasInscrit()
    {
        return $this->pasInscrit;
    }

    /**
     * @param mixed $pasInscrit
     */
    public function setPasInscrit($pasInscrit): void
    {
        $this->pasInscrit = $pasInscrit;
    }

    /**
     * @return mixed
     */
    public function getPassees()
    {
        return $this->passees;
    }

    /**
     * @param mixed $passees
     */
    public function setPassees($passees): void
    {
        $this->passees = $passees;
    }
}

