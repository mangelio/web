<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConstructionSiteVoter extends BaseVoter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string           $attribute An attribute
     * @param ConstructionSite $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof ConstructionSite;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string           $attribute
     * @param ConstructionSite $subject
     * @param TokenInterface   $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $this->getUser($token);

        if (!$user instanceof ConstructionManager) {
            return false;
        }

        return $subject->getConstructionManagers()->contains($user);
    }
}
