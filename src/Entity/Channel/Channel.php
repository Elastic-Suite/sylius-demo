<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Gally\SyliusPlugin\Model\GallyChannelInterface;
use Sylius\Component\Core\Model\Channel as BaseChannel;
use Gally\SyliusPlugin\Model\GallyChannelTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements GallyChannelInterface
{
    use GallyChannelTrait;
}
