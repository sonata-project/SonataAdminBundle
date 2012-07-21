<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Export;

use Exporter\Source\SourceIteratorInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Exporter
{
    /**
     * @throws \RuntimeException
     *
     * @param string                  $format
     * @param string                  $filename
     * @param SourceIteratorInterface $source
     *
     * @return StreamedResponse
     */
    public function getResponse($format, $filename, SourceIteratorInterface $source)
    {
        switch ($format) {
            case 'xls':
                $writer      = new \Exporter\Writer\XlsWriter('php://output');
                $contentType = 'application/vnd.ms-excel';
                break;
            case 'xml':
                $writer      = new \Exporter\Writer\XmlWriter('php://output');
                $contentType = 'text/xml';
                break;
            case 'json':
                $writer      = new \Exporter\Writer\JsonWriter('php://output');
                $contentType = 'application/json';
                break;
            case 'csv':
                $writer      = new \Exporter\Writer\CsvWriter('php://output', ',', '"', "", true);
                $contentType = 'text/csv';
                break;
            default:
                throw new \RuntimeException('Invalid format');
        }

        $callback = function() use ($source, $writer) {
            $handler = \Exporter\Handler::create($source, $writer);
            $handler->export();
        };

        return new StreamedResponse($callback, 200, array(
            'Content-Type'        => $contentType,
            'Content-Disposition' => sprintf('attachment; filename=%s', $filename)
        ));
    }
}