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

namespace Sonata\AdminBundle\Tests\Functional\Translator\Extractor;

use Sonata\AdminBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AdminExtractorTest extends KernelTestCase
{
    public function testDebugMissingMessages(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['locale' => 'en']);

        self::assertMatchesRegularExpression('/group_label/', $tester->getDisplay());
        self::assertMatchesRegularExpression('/admin_label/', $tester->getDisplay());
        self::assertMatchesRegularExpression('/Name Show/', $tester->getDisplay());
        self::assertMatchesRegularExpression('/Name List/', $tester->getDisplay());
        self::assertMatchesRegularExpression('/Name Form/', $tester->getDisplay());
        self::assertMatchesRegularExpression('/Date Published/', $tester->getDisplay());
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    private function createCommandTester(): CommandTester
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        return new CommandTester($application->find('debug:translation'));
    }
}
