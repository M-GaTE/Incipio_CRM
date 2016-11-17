<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MgateUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

    public function __construct()
    {
        $this->name = 'mgateUserBundle';
    }

}
