<?php

declare(strict_types=1);

namespace App\Foundry\Story;

use Akawakaweb\SyliusFixturesPlugin\Foundry\Factory\LocaleFactory;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Story\DefaultLocalesStoryInterface;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\Story;

final class DefaultLocalesStory extends Story implements DefaultLocalesStoryInterface
{
    public function __construct(
    ) {
    }

    public function build(): void
    {
        Factory::delayFlush(function () {
            foreach ($this->getLocaleCodes() as $currencyCode) {
                LocaleFactory::new()->withCode($currencyCode)->create();
            }
        });
    }

    /**
     * @return string[]
     */
    private function getLocaleCodes(): array
    {
        return ['en_US', 'fr_FR'];
    }
}
