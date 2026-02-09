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
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use SensioLabs\AdminBundle\User\Action\CheckEmailAction;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

final class CheckEmailActionTest extends TestCase
{
    public function testRendersCheckEmailPage(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<html>check email</html>');

        $pool = new Pool($this->createMock(ContainerInterface::class));
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);

        $action = new CheckEmailAction($twig, $pool, $templateRegistry, 86400);
        $response = $action(new Request());

        self::assertSame(200, $response->getStatusCode());
    }
}
