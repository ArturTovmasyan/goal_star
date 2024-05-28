<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 2/5/16
 * Time: 4:31 PM
 */

namespace LB\UserBundle\Doctrine;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class UserManager
 * @package LB\UserBundle\Doctrine
 */
class UserManager extends BaseUserManager
{

    /**
     * @param string $usernameOrEmail
     * @return UserInterface
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        // get filters
        $filters = $this->objectManager->getFilters();

        // disable filter
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;

       return parent::findUserByUsernameOrEmail($usernameOrEmail);
    }
}