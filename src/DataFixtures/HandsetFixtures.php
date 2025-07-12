<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Handset;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class HandsetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $featuresList = [
            '5G',
            'wireless_charging',
            'face_recognition',
            'stylus',
            'water_resistant',
            'ai_features',
            'dual_sim'
        ];

        // Keys for the brands as saved in BrandFixtures
        $brandKeys = array_keys(\App\DataFixtures\BrandFixtures::BRAND_REFERENCES);

        for ($i = 0; $i < 55; $i++) {
            $handset = new Handset();
            $handset->setName($faker->unique()->word() . ' ' . $faker->randomNumber(2) . ' Pro');

            // Assign a random brand reference
            $brandRefKey = 'brand_' . $brandKeys[array_rand($brandKeys)];
            $handset->setBrand($this->getReference($brandRefKey, Brand::class));

            $handset->setPrice($faker->randomFloat(2, 199, 1499));
            $handset->setReleaseDate($faker->dateTimeBetween('-3 years', 'now'));
            $handset->setFeatures($faker->randomElements($featuresList, rand(2, 4)));
            $handset->setSpecifications([
                'screen_size' => $faker->randomElement(['6.1', '6.4', '6.7', '6.8']),
                'battery' => $faker->numberBetween(3000, 5000) . 'mAh',
                'camera' => $faker->randomElement(['12MP', '48MP', '50MP', '108MP', '200MP']),
                'storage_options' => [$faker->randomElement(['64GB', '128GB', '256GB', '512GB', '1TB'])],
                'ram' => $faker->randomElement(['6GB', '8GB', '12GB']),
            ]);
            $handset->setDescription($faker->sentence(8));
            $manager->persist($handset);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            \App\DataFixtures\BrandFixtures::class,
        ];
    }
}
