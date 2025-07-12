<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BrandFixtures extends Fixture
{
    public const BRAND_REFERENCES = [
        'apple' => ['Apple', 'USA'],
        'samsung' => ['Samsung', 'South Korea'],
        'google' => ['Google', 'USA'],
        'xiaomi' => ['Xiaomi', 'China'],
        'oneplus' => ['OnePlus', 'China'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::BRAND_REFERENCES as $refKey => [$name, $country]) {
            $brand = new Brand();
            $brand->setName($name);
            $brand->setCountry($country);
            $manager->persist($brand);

            // Save reference for HandsetFixtures
            $this->addReference('brand_' . $refKey, $brand);
        }

        $manager->flush();
    }
}
