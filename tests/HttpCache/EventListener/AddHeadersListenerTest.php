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

namespace ApiPlatform\Tests\HttpCache\EventListener;

use ApiPlatform\HttpCache\EventListener\AddHeadersListener;
use ApiPlatform\Tests\Fixtures\TestBundle\Entity\Dummy;
use ApiPlatform\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AddHeadersListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testDoNotSetHeaderWhenMethodNotCacheable()
    {
        $request = new Request([], [], ['_api_resource_class' => Dummy::class, '_api_operation_name' => 'get']);
        $request->setMethod('PUT');
        $response = new Response();
        $event = new ResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $listener = new AddHeadersListener(true);
        $listener->onKernelResponse($event);

        $this->assertNull($response->getEtag());
    }

    public function testDoNotSetHeaderOnUnsuccessfulResponse()
    {
        $request = new Request([], [], ['_api_resource_class' => Dummy::class, '_api_operation_name' => 'get']);
        $response = new Response('{}', Response::HTTP_BAD_REQUEST);
        $event = new ResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $listener = new AddHeadersListener(true);
        $listener->onKernelResponse($event);

        $this->assertNull($response->getEtag());
    }

    public function testDoNotSetHeaderWhenNotAnApiOperation()
    {
        $response = new Response();
        $event = new ResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request(),
            \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $listener = new AddHeadersListener(true);
        $listener->onKernelResponse($event);

        $this->assertNull($response->getEtag());
    }

    public function testDoNotSetHeaderWhenNoContent()
    {
        $response = new Response();
        $event = new ResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request([], [], ['_api_resource_class' => Dummy::class, '_api_operation_name' => 'get']),
            \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::MASTER_REQUEST,
            $response
        );
        $listener = new AddHeadersListener(true);
        $listener->onKernelResponse($event);

        $this->assertNull($response->getEtag());
    }

    public function testAddHeaders()
    {
        $response = new Response('some content', 200, ['Vary' => ['Accept', 'Cookie']]);
        $event = new ResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request([], [], ['_api_resource_class' => Dummy::class, '_api_operation_name' => 'get']),
            \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $listener = new AddHeadersListener(true, 100, 200, ['Accept', 'Accept-Encoding'], true, 15, 30);
        $listener->onKernelResponse($event);

        $this->assertSame('"9893532233caff98cd083a116b013c0b"', $response->getEtag());
        $this->assertSame('max-age=100, public, s-maxage=200, stale-if-error=30, stale-while-revalidate=15', $response->headers->get('Cache-Control'));
        $this->assertSame(['Accept', 'Cookie', 'Accept-Encoding'], $response->getVary());
    }

    public function testDoNotSetHeaderWhenAlreadySet()
    {
        $response = new Response('some content', 200, ['Vary' => ['Accept', 'Cookie']]);
        $response->setEtag('etag');
        $response->setMaxAge(300);
        // This also calls setPublic
        $response->setSharedMaxAge(400);

        $event = new ResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request([], [], ['_api_resource_class' => Dummy::class, '_api_operation_name' => 'get']),
            \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $listener = new AddHeadersListener(true, 100, 200, ['Accept', 'Accept-Encoding'], true, 15, 30);
        $listener->onKernelResponse($event);

        $this->assertSame('"etag"', $response->getEtag());
        $this->assertSame('max-age=300, public, s-maxage=400, stale-if-error=30, stale-while-revalidate=15', $response->headers->get('Cache-Control'));
        $this->assertSame(['Accept', 'Cookie', 'Accept-Encoding'], $response->getVary());
    }
}
