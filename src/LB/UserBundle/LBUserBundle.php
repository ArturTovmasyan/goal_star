<?php

namespace LB\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LBUserBundle extends Bundle
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
