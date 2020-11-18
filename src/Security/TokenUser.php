<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\Craftsman;
use App\Entity\Filter;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenUser implements UserInterface
{
    public const ROLE_API_USER = 'ROLE_API_USER';

    /**
     * @var Craftsman
     */
    private $craftsman;

    /**
     * @var Filter
     */
    private $filter;

    public static function createForCraftsman(Craftsman $craftsman)
    {
        $self = new self();
        $self->craftsman = $craftsman;

        return $self;
    }

    public static function createForFilter(Filter $filter)
    {
        $self = new self();
        $self->filter = $filter;

        return $self;
    }

    public function getRoles()
    {
        return [self::ROLE_API_USER];
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
    }

    public function eraseCredentials()
    {
    }
}