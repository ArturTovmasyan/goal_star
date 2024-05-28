<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 11/26/15
 * Time: 4:03 PM
 */

namespace LB\UserBundle\Security;

use AppBundle\Entity\LBGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use LB\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{

    const VIEW = 'view';
    const EDIT = 'edit';

//    protected function getSupportedAttributes()
//    {
//        return array(self::VIEW, self::EDIT);
//    }
//
//    protected function getSupportedClasses()
//    {
//        return array('AppBundle\Entity\LBGroup');
//    }

    public function supports($attribute, $subject)
    {
        return $subject instanceof LBGroup && in_array($attribute, array(
            self::VIEW, self::EDIT
        ));
    }

    protected function voteOnAttribute($attribute, $post, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // double-check that the User object is the expected entity (this
        // only happens when you did not configure the security system properly)
        if (!$user instanceof User) {
            throw new \LogicException('The user is somehow not our User class!');
        }

        switch($attribute) {

            case self::VIEW:
                // the data object could have for example a method isPrivate()
                // which checks the Boolean attribute $private

                if ($post->getType() == 1) {

                    if($post->isAuthor($user) || $post->isModerator($user) || $post->isMember($user))
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return true;
                }
                break;
            case self::EDIT:
                // this assumes that the data object has a getOwner() method
                // to get the entity of the user who owns this data object
                if ($post->isAuthor($user) || $post->isModerator($user)) {

                    return true;
                }
                else
                {
                    return false;
                }
                break;
        }

        return false;
    }

}