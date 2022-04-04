<?php

namespace App\Model;


class RechercheCampus{
    private $nom;

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom): void
    {
        $this->nom = $nom;
    }
}