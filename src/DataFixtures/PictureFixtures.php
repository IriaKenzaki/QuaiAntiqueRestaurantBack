<?php

namespace App\DataFixtures;

use App\Entity\Picture;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;


class PictureFixtures extends Fixture implements DependentFixtureInterface
{

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for($i =1; $i<= 20; $i++){
            /** @var Restaurant $restaurant */
            $restaurant = $this->getReference(RestaurantFixtures::RESTAURANT_REFERENCE. random_int(1,20));
            $picture = (new Picture())
                ->setTitle("Titre $i")
                ->setSlug("slug-article-title")
                ->setRestaurant($restaurant)
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($picture);
        }

        $manager->flush();
    }
    public function getDependencies():array
    {
        return[RestaurantFixtures::class];
    }
}
