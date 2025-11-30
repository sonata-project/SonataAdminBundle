<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * TODO: remove when support for Symfony < 7.4 is dropped.
 */
final class CommandHelper
{
    public static function addCommandToApplication(Application $application, callable|Command $command): void
    {
        /* @phpstan-ignore-next-line function.alreadyNarrowedType */
        if (method_exists($application, 'addCommand')) {
            $application->addCommand($command);
        } else {
            /**
             * @psalm-suppress PossiblyInvalidArgument
             *
             * @phpstan-ignore argument.type
             */
            $application->add($command);
        }
    }
}
