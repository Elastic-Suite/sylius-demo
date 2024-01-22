<?php

declare(strict_types=1);

namespace App\Foundry\Story;

use Akawakaweb\SyliusFixturesPlugin\Foundry\Story\RandomCapsStoryInterface;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Story\RandomJeansStoryInterface;
use Akawakaweb\SyliusFixturesPlugin\Foundry\Story\RandomTShirtsStoryInterface;
use Zenstruck\Foundry\Story;

final class EmptyStory extends Story implements RandomCapsStoryInterface, RandomJeansStoryInterface, RandomTShirtsStoryInterface
{
    public function __construct()
    {
    }

    public function build(): void
    {
    }
}
