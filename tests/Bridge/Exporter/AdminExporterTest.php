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

namespace Sonata\AdminBundle\Tests\Bridge\Exporter;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Writer\TypedWriterInterface;

final class AdminExporterTest extends TestCase
{
    /**
     * @phpstan-return iterable<array-key, array{string[], string[], string[]}>
     */
    public function provideExportFormats(): iterable
    {
        yield 'no override' => [['xls'], [], ['xls']];
        yield 'override in admin' => [['csv'], ['csv'], ['xls']];
    }

    /**
     * @param string[] $expectedFormats
     * @param string[] $adminFormats
     * @param string[] $globalFormats
     *
     * @dataProvider provideExportFormats
     */
    public function testAdminHasPriorityOverGlobalSettings(array $expectedFormats, array $adminFormats, array $globalFormats): void
    {
        $writers = [];
        foreach ($globalFormats as $exportFormat) {
            $writer = $this->createMock(TypedWriterInterface::class);
            $writer->expects(static::once())
                ->method('getFormat')
                ->willReturn($exportFormat);
            $writers[] = $writer;
        }

        $exporter = new Exporter($writers);
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(static::once())
            ->method('getExportFormats')
            ->willReturn($adminFormats);
        $adminExporter = new AdminExporter($exporter);
        static::assertSame($expectedFormats, $adminExporter->getAvailableFormats($admin));
    }

    public function testGetExportFilename(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(static::once())
            ->method('getClass')
            ->willReturn('MyProject\AppBundle\Model\MyClass');
        $adminExporter = new AdminExporter(new Exporter());
        static::assertMatchesRegularExpression(
            '#export_myclass_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}.csv#',
            $adminExporter->getExportFilename($admin, 'csv')
        );
    }
}
