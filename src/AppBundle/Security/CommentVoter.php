<?php

namespace AppBundle\Security;

use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    const DELETE = 'delete';
    const EDIT = 'edit';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::DELETE, self::EDIT))) {
            return false;
        }

        if (!$subject instanceof Comment) {
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

        if (!$user instanceof User) {
            return false;
        }

        $comment = $subject;

        switch($attribute) {
            case self::DELETE:
                return $this->canDelete($comment, $user);
            case self::EDIT:
                return $this->canEdit($comment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete(Comment $comment, User $user)
    {
        return $comment->getAuthor() == $user;
    }

    private function canEdit(Comment $comment, User $user)
    {
        return $comment->getAuthor() == $user;
    }
}