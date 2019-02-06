<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Core\Event;

use ApiPlatform\Core\Events;
use Symfony\Component\EventDispatcher\Event;

final class PostDeserializeEvent extends Event
{
    const NAME = Events::POST_DESERIALIZE;
    private $data;

    public function __construct($controllerResult)
    {
        $this->data = $controllerResult;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }
}
