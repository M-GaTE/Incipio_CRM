<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MgatePersonneBundle extends Bundle
{
    public function __construct()
    {
        $this->name = 'MgatePersonneBundle';
    }
}
