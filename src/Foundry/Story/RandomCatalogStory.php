<?php

declare(strict_types=1);

namespace App\Foundry\Story;

use Akawakaweb\SyliusFixturesPlugin\Foundry\Factory\ProductAttributeFactory;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Factory\ProductFactory;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Factory\ProductOptionFactory;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Factory\TaxonFactory;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Story\RandomDressesStoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Sylius\Component\Attribute\AttributeType\IntegerAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Zenstruck\Foundry\Story;
use Faker\Factory;

final class RandomCatalogStory extends Story implements RandomDressesStoryInterface
{
    private const CATEGORY_BY_LEVEL_COUNT = 10;
    private const CATEGORY_LEVEL_COUNT = 3;

    private const SCALAR_ATTRIBUTE_COUNT = 500;
    private const SELECT_ATTRIBUTE_COUNT = 500;
    private const MAX_ATTRIBUTE_OPTION_COUNT = 200;

    private const PRODUCT_COUNT = 100000;

    private Generator $faker;
    private array $taxa = [];
    private array $scalarAttributes = [];
    private array $selectAttributes = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->faker = Factory::create();
    }

    public function build(): void
    {
        print("\nCreate taxa");
        $this->createTaxa();
        print("\nCreate attributes");
        $this->createAttributes();
        print("\nCreate products");
        $this->createProducts();
        print("\n");
    }

    private function createTaxa(string $parentCode = null, int $level = 1): array
    {
        $categories = [];
        for ($index = 0; $index < self::CATEGORY_BY_LEVEL_COUNT; $index++) {
            $slug = $this->faker->slug(4, false);
            $code = $parentCode . '/' . str_replace(' ', '_', $slug) . $index;
            if ($level === 1) {
                print(".");
                TaxonFactory::new()
                    ->withCode('MENU_CATEGORY')
                    ->withName('Category')
                    ->withTranslations([
                        'en_US' => ['name' => 'Category'],
                        'fr_FR' => ['name' => 'Catégorie'],
                    ])
                    ->withChildren([
                        [
                            'code' => $code,
                            'name' => ucfirst($slug) . "$level$index",
                            'translations' => [
                                'en_US' => ['name' => ucfirst($slug) . "US $level$index"],
                                'fr_FR' => ['name' => ucfirst($slug) . "FR $level$index"],
                            ],
                            'children' => $this->createTaxa($code, $level+1),
                        ]
                    ])
                    ->create();
            } else {
                $category = [
                    'code' => $code,
                    'name' => ucfirst($slug) . "$level$index",
                    'translations' => [
                        'en_US' => ['name' => ucfirst($slug) . "US $level$index"],
                        'fr_FR' => ['name' => ucfirst($slug) . "FR $level$index"],
                    ]
                ];
                if (self::CATEGORY_LEVEL_COUNT > $level) {
                    $category['children'] = $this->createTaxa($code, $level+1);
                }
                $categories[] = $category;

                if (self::CATEGORY_LEVEL_COUNT === $level) {
                    $this->taxa[$code] = $code;
                }
            }
        }

        return $categories;
    }

    private function createAttributes(): void
    {
        $scalarTypes = [TextAttributeType::TYPE, IntegerAttributeType::TYPE];

        for ($index = 0; $index < self::SCALAR_ATTRIBUTE_COUNT; $index++) {
            $code = $this->faker->slug(3, false) . $index;
            $type = $scalarTypes[array_rand($scalarTypes)];
            ProductAttributeFactory::new()
                ->withCode(str_replace(' ', '_', $code))
                ->withName(ucfirst($code))
                ->withType($type)
                ->create();
            $this->scalarAttributes[$type][$code] = $code;

            if (($index % 200) === 0) {
                print(".");
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        print("\n");
        for ($index = 0; $index < self::SELECT_ATTRIBUTE_COUNT; $index++) {
            $code = $this->faker->slug(1) . $index;
            $options = [];
            for (
                $optionIndex = 0;
                $optionIndex < rand((int) ceil(self::MAX_ATTRIBUTE_OPTION_COUNT/10), self::MAX_ATTRIBUTE_OPTION_COUNT);
                $optionIndex++
            ) {
                $optionCode = $this->faker->slug(1) . $optionIndex;
                $options[$code . '-' . $optionCode] = ucfirst($optionCode);
            }

            ProductOptionFactory::new()
                ->withCode($code)
                ->withName(ucfirst($code))
                ->withValues($options)
                ->create();
            $this->selectAttributes[$code] = $code;

            if (($index % 200) === 0) {
                print(".");
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
    }

    private function createProducts(): void
    {
        for ($index = 0; $index < self::PRODUCT_COUNT; $index++) {
            $taxon = array_rand($this->taxa);
            $sku = $this->faker->slug(1) . '-' . $index;

            $attributes = [];
            foreach (array_rand($this->scalarAttributes[IntegerAttributeType::TYPE], 20) as $code) {
                $attributes[$code] = rand(1, 100);
            }
            foreach (array_rand($this->scalarAttributes[TextAttributeType::TYPE], 20) as $code) {
                $attributes[$code] = $this->faker->slug(2);
            }

            $product = ProductFactory::new()
                ->withName($this->faker->slug(5) . " $index")
                ->withCode($sku)
                ->withTaxCategory('clothing')
                ->withChannels(['FASHION_WEB'])
                ->withMainTaxon($taxon)
                ->withTaxa([$taxon])
                ->withProductAttributes($attributes);

            if ($index % 2) {
                $product->withProductOptions(array_rand($this->selectAttributes, 2));
            }

            if (($index % 1000) === 0) {
                print(".");
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $product->create();
        }
    }
}
