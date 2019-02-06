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

use Symfony\Component\EventDispatcher\Event;

final class PostSerializeEvent extends Event
{
    const NAME = ApiPlatformEvents::POST_SERIALIZE;
    private $controllerResult;

    public function __construct($controllerResult)
    {
        $this->controllerResult = $controllerResult;
    }

    public function getControllerResult()
    {
        return $this->controllerResult;
    }

    public function setControllerResult($controllerResult): void
    {
        $this->controllerResult = $controllerResult;
    }
}
