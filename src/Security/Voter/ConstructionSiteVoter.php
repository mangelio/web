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
use App\Entity\Craftsman;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConstructionSiteVoter extends Voter
{
    const CONSTRUCTION_SITE_CREATE = 'CONSTRUCTION_SITE_CREATE';
    const CONSTRUCTION_SITE_VIEW = 'CONSTRUCTION_SITE_VIEW';
    const CONSTRUCTION_SITE_MODIFY = 'CONSTRUCTION_SITE_MODIFY';

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
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::CONSTRUCTION_SITE_CREATE, self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY])) {
            return false;
        }

        return $subject instanceof ConstructionSite;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string           $attribute
     * @param ConstructionSite $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof ConstructionManager) {
            if ($user->getIsTrialAccount() || $user->getIsExternalAccount()) {
                return $subject->getConstructionManagers()->contains($user);
            } else {
                if (self::CONSTRUCTION_SITE_MODIFY === $attribute) {
                    return $subject->getConstructionManagers()->contains($user);
                }

                return true;
            }
        } elseif ($user instanceof Craftsman) {
            switch ($attribute) {
                case self::CONSTRUCTION_SITE_VIEW:
                    return $user->getConstructionSite() === $subject;
                case self::CONSTRUCTION_SITE_MODIFY:
                case self::CONSTRUCTION_SITE_CREATE:
                    return false;
            }
        }

        throw new \LogicException('Attribute '.$attribute.' unknown!');
    }
}
