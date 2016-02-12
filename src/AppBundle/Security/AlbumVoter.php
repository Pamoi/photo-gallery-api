<?php

namespace AppBundle\Security;

use AppBundle\Entity\Album;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AlbumVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const COMMENT = 'comment';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE, self::COMMENT))) {
            return false;
        }

        if (!$subject instanceof Album) {
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

        $album = $subject;

        switch($attribute) {
            case self::VIEW:
                return $this->canView($album, $user);
            case self::EDIT:
                return $this->canEdit($album, $user);
            case self::DELETE:
                return $this->canDelete($album, $user);
            case self::COMMENT:
                return $this->canComment($album, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Album $album, User $user = null)
    {
        if ($this->canEdit($album, $user)) {
            return true;
        }

        return $album->isPublic();
    }

    private function canEdit(Album $album, User $user = null)
    {
        return $album->getAuthors()->contains($user);
    }

    private function canDelete(Album $album, User $user = null)
    {
        return $album->getAuthors()->contains($user);
    }

    private function canComment(Album $album, User $user = null)
    {
        return $this->canView($album, $user);
    }
}