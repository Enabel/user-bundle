<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Tests;

use Enabel\UserBundle\EnabelUserBundle;
use PHPUnit\Framework\TestCase;

class EnabelUserBundleTest extends TestCase
{
    public function testGetPath(): void
    {
        $this->assertSame(\dirname(__DIR__), (new EnabelUserBundle())->getPath());
    }
}
