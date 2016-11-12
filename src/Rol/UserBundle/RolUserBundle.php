<?php

namespace Rol\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RolUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}   
