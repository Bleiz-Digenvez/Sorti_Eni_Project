<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantInscritVoter extends Voter
{
    public const SORTIE_INSCRIPTION = 'sortie_inscription_voter';
    public const SORTIE_DESISTEMENT = 'sortie_desister_voter';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $sortie): bool
    {
        //On verifie qu'on envoi les bonnes infos
        return in_array($attribute, [self::SORTIE_INSCRIPTION, self::SORTIE_DESISTEMENT])
            && $sortie instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, $sortie, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // On verifie si le user est connecté
        if (!$user instanceof UserInterface) {
            return false;
        }

        //retourne le boolean selon l'attribut passé
        switch ($attribute) {
            case self::SORTIE_INSCRIPTION:
                if (!$this->estIsncrit($sortie, $token)) return true;
                break;
            case self::SORTIE_DESISTEMENT:
                if ($this->estIsncrit($sortie, $token)) return true;
                break;
        }
        return false;
    }

    /**
     * Test si le participant connecté est inscrit à la sortie
     * @param Sortie $sortie
     * @param TokenInterface $token
     * @return bool
     */
    private function estIsncrit(Sortie $sortie, TokenInterface $token) {
        $estInscrit = false;
        foreach ($sortie->getParticipants() as $participant){
            if ($participant->getPassword() == $token->getUser()->getPassword()){
                $estInscrit = true;
            }
        }
        return $estInscrit;
    }
}
