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

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AdminExtractorTest extends KernelTestCase
{
    public function testDebugMissingMessages(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['locale' => 'en']);

        $this->assertMatchesRegularExpression('/group_label/', $tester->getDisplay());
        $this->assertMatchesRegularExpression('/admin_label/', $tester->getDisplay());
        $this->assertMatchesRegularExpression('/Name Show/', $tester->getDisplay());
        $this->assertMatchesRegularExpression('/Name List/', $tester->getDisplay());
        $this->assertMatchesRegularExpression('/Name Form/', $tester->getDisplay());
        $this->assertMatchesRegularExpression('/Date Published/', $tester->getDisplay());
    }

    private function createCommandTester(): CommandTester
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        return new CommandTester($application->find('debug:translation'));
    }
}
