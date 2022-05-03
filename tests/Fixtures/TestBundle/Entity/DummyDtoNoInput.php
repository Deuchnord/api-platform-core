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

namespace ApiPlatform\Tests\Fixtures\TestBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Tests\Fixtures\TestBundle\Controller\DummyDtoNoInput\CreateItemAction;
use ApiPlatform\Tests\Fixtures\TestBundle\Controller\DummyDtoNoInput\DoubleBatAction;
use ApiPlatform\Tests\Fixtures\TestBundle\Dto\OutputDto;
use Doctrine\ORM\Mapping as ORM;

/**
 * DummyDtoNoInput.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 * @ORM\Entity
 */
#[ApiResource(operations: [new Get(), new Delete(), new Post(uriTemplate: '/dummy_dto_no_inputs/{id}/double_bat', controller: DoubleBatAction::class, status: 200), new Post(uriTemplate: '/dummy_dto_no_inputs', controller: CreateItemAction::class), new GetCollection()], input: false, output: OutputDto::class)]
class DummyDtoNoInput
{
    /**
     * @var int The id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column
     */
    public $lorem;
    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    public $ipsum;

    public function getId()
    {
        return $this->id;
    }
}
