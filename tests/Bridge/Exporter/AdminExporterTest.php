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

class AdminExporterTest extends TestCase
{
    public function provideExportFormats()
    {
        return [
            'no override' => [['xls'], null, ['xls']],
            'override in admin' => [['csv'], ['csv'], ['xls']],
        ];
    }

    /**
     * @dataProvider provideExportFormats
     */
    public function testAdminHasPriorityOverGlobalSettings(array $expectedFormats, ?array $adminFormats, array $globalFormats): void
    {
        $writers = [];
        foreach ($globalFormats as $exportFormat) {
            $writer = $this->createMock(TypedWriterInterface::class);
            $writer->expects($this->once())
                ->method('getFormat')
                ->willReturn($exportFormat);
            $writers[] = $writer;
        }

        $exporter = new Exporter($writers);
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())
            ->method('getExportFormats')
            ->willReturn($adminFormats);
        $adminExporter = new AdminExporter($exporter);
        $this->assertSame($expectedFormats, $adminExporter->getAvailableFormats($admin));
    }

    public function testGetExportFilename(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->once())
            ->method('getClass')
            ->willReturn('MyProject\AppBundle\Model\MyClass');
        $adminExporter = new AdminExporter(new Exporter());
        $this->assertRegExp(
            '#export_myclass_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}.csv#',
            $adminExporter->getExportFilename($admin, 'csv')
        );
    }
}
