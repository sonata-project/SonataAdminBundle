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

namespace Sonata\AdminBundle\Bridge\Exporter;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\Exporter\Exporter;

/**
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
final class AdminExporter
{
    /**
     * @var Exporter service from the exporter bundle
     */
    private $exporter;

    /**
     * @param Exporter $exporter will be used to get global settings
     */
    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * Queries an admin for its default export formats, and falls back on global settings.
     *
     * @return string[] an array of formats
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    public function getAvailableFormats(AdminInterface $admin): array
    {
        $adminExportFormats = $admin->getExportFormats();

        if ([] !== $adminExportFormats) {
            return $adminExportFormats;
        }

        return $this->exporter->getAvailableFormats();
    }

    /**
     * Builds an export filename from the class associated with the provided admin,
     * the current date, and the provided format.
     *
     * @param string $format the format of the export file
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    public function getExportFilename(AdminInterface $admin, string $format): string
    {
        $class = $admin->getClass();
        $namespaceSeparatorPos = strripos($class, '\\');

        return sprintf(
            'export_%s_%s.%s',
            strtolower(false !== $namespaceSeparatorPos ? substr($class, $namespaceSeparatorPos + 1) : $class),
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );
    }
}
