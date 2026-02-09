<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\User\Action;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\User\Action\LogoutAction;

final class LogoutActionTest extends TestCase
{
    public function testThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $action = new LogoutAction();
        $action();
    }
}
