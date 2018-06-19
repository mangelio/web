<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use Doctrine\Common\Persistence\ObjectManager;

class LoadConstructionSiteData extends BaseFixture
{
    const ORDER = LoadConstructionManagerData::ORDER + 1;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $entries = [
            ["Sun Park", "Parkstrasse 12", 7270, "Davos", "CH"]
        ];

        $appUsers = $manager->getRepository(ConstructionManager::class)->findAll();
        foreach ($entries as $entry) {
            $building = new ConstructionSite();
            $building->setName($entry[0]);
            $building->setStreetAddress($entry[1]);
            $building->setPostalCode($entry[2]);
            $building->setLocality($entry[3]);
            $building->setCountry($entry[4]);
            $manager->persist($building);


            foreach ($appUsers as $appUser) {
                $building->getConstructionManagers()->add($appUser);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}