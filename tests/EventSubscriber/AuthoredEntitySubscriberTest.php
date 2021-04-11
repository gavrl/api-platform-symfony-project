<?php

namespace App\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use App\EventSubscriber\AuthoredEntitySubscriber;
use ApiPlatform\Core\EventListener\EventPriorities;

class AuthoredEntitySubscriberTest extends TestCase
{
    public function testConfiguration()
    {
        $result = AuthoredEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::VIEW, $result);
        $this->assertEquals(
            ['getAuthenticatedUser', EventPriorities::PRE_WRITE],
            $result[KernelEvents::VIEW]
        );
    }
}