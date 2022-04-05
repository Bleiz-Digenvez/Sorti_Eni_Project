<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;


class ParticipantInscritVoter extends Voter
{
    public const SORTIE_INSCRIPTION = 'sortie_inscription_voter';
    public const SORTIE_DESISTEMENT = 'sortie_desister_voter';
    public const SORTIE_PUBLIER = 'sortie_publier_voter';
    public const SORTIE_ANNULER = 'sortie_annuler_voter';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $sortie): bool
    {
        //On verifie qu'on envoi les bonnes infos
        return in_array($attribute, [self::SORTIE_INSCRIPTION, self::SORTIE_DESISTEMENT, self::SORTIE_PUBLIER, self::SORTIE_ANNULER])
            && $sortie instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, $sortie, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // On verifie si le user est connecté
        if (!$user instanceof Participant) {
            return false;
        }

        //retourne le boolean selon l'attribut passé
        switch ($attribute) {
            case self::SORTIE_INSCRIPTION:
                    if ($this->peutSincrire($sortie, $token))  return true;
                break;
            case self::SORTIE_DESISTEMENT:
                    if ($this->peutSeDesister($sortie, $token)) return true;
                break;
            case self::SORTIE_PUBLIER:
                    if ($this->publier($sortie, $token)) return true;
                break;
            case self::SORTIE_ANNULER:
                    if ($this->annuler($sortie, $token)) return true;
                break;
        }
        return false;
    }

    /**
     * Teste si le participant connecté peut  s'inscrit à la sortie
     * @param Sortie $sortie
     * @param TokenInterface $token
     * @return bool
     */
    private function peutSincrire(Sortie $sortie, TokenInterface $token) {
        $estInscrit = false;
        foreach ($sortie->getParticipants() as $participant){
            if ($participant->getPassword() == $token->getUser()->getPassword()){
                $estInscrit = true;
            }
        }
        if ($sortie->getEtat()->getLibelle() == "Ouverte"
            && ($sortie->getDateLimiteInscription()) > (new \DateTime('now'))
            && !$estInscrit
        ){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Teste si le participant connecté peut se disister de la sortie
     * @param Sortie $sortie
     * @param TokenInterface $token
     * @return bool
     */
    private function peutSeDesister(Sortie $sortie, TokenInterface $token) {
        $estInscrit = false;
        foreach ($sortie->getParticipants() as $participant){
            if ($participant->getPassword() == $token->getUser()->getPassword()){
                $estInscrit = true;
            }
        }
        if ($sortie->getDateHeureDebut() > (new \DateTime('now'))
            && (in_array($sortie->getEtat()->getLibelle(),['Ouverte','Clôturée']))
            && $estInscrit
        ){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Teste si le participant connecté est organisateur de la sortie ou admin
     * et si la sortie est à l'état de "créée"
     * @param Sortie $sortie
     * @param TokenInterface $token
     * @return bool
     */
    private function publier(Sortie $sortie, TokenInterface $token) {
        if ( ( ($sortie->getOrganisateur()->getPassword() == $token->getUser()->getPassword())
            || ($this->security->isGranted("ROLE_ADMIN")) )
            && $sortie->getEtat()->getLibelle() == "Créée"
        ){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Teste si le participant connecté est organisateur de la sortie
     * et si la sortie est à l'état de "Créée" ou "Ouverte" ou "Clôturée"
     * @param Sortie $sortie
     * @param TokenInterface $token
     * @return bool
     */
    private function annuler(Sortie $sortie, TokenInterface $token) {
        if ( ($sortie->getOrganisateur()->getPassword() == $token->getUser()->getPassword()
                || $this->security->isGranted("ROLE_ADMIN"))
            && (in_array($sortie->getEtat()->getLibelle(), ["Créée", "Ouverte", "Clôturée"]))
        ){
            return true;
        } else {
            return false;
        }
    }
}
