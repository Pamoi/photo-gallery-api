<?php

namespace AppBundle\Security;

use AppBundle\Entity\Photo;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PhotoVoter extends Voter
{
    const VIEW = 'view';
    const DELETE = 'delete';
    const COMMENT = 'comment';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::DELETE, self::COMMENT))) {
            return false;
        }

        if (!$subject instanceof Photo) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        $user = $token->getUser();
        if ($user == 'anon.') {
            $user = null;
        }

        if (!$user instanceof User && $user !== null) {
            return false;
        }

        $photo = $subject;

        switch($attribute) {
            case self::VIEW:
                return $this->canView($photo, $user);
            case self::DELETE:
                return $this->canDelete($photo, $user);
            case self::COMMENT:
                return $this->canComment($photo, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Photo $photo, User $user = null)
    {
        if ($this->canDelete($photo, $user)) {
            return true;
        }

        return $photo->getAlbum()->isPublic();
    }

    private function canDelete(Photo $photo, User $user = null)
    {
        if ($photo->getAuthor() == $user) {
            return true;
        }

        if ($photo->getAlbum()->getAuthors()->contains($user)) {
            return true;
        }

        return false;
    }

    private function canComment(Photo $photo, User $user = null)
    {
        return $this->canView($photo, $user);
    }
}