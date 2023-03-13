<?php

namespace App\DataFixtures;

use App\Entity\Board;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $board = new Board();

        $manager->flush();
    }
}
