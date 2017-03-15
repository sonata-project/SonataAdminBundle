<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Bridge\Exporter;

use Exporter\Exporter;
use Sonata\AdminBundle\Admin\AdminInterface;

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
     * @param Exporter will be used to get global settings
     */
    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * Queries an admin for its default export formats, and falls back on global settings.
     *
     * @param AdminInterface $admin the current admin object
     *
     * @return string[] an array of formats
     */
    public function getAvailableFormats(AdminInterface $admin)
    {
        $adminExportFormats = $admin->getExportFormats();

        // NEXT_MAJOR : compare with null
        if ($adminExportFormats != array('json', 'xml', 'csv', 'xls')) {
            return $adminExportFormats;
        }

        return $this->exporter->getAvailableFormats();
    }

    /**
     * Builds an export filename from the class associated with the provided admin,
     * the current date, and the provided format.
     *
     * @param AdminInterface $admin  the current admin object
     * @param string         $format the format of the export file
     */
    public function getExportFilename(AdminInterface $admin, $format)
    {
        $class = $admin->getClass();

        return sprintf(
            'export_%s_%s.%s',
            strtolower(substr($class, strripos($class, '\\') + 1)),
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );
    }
}
