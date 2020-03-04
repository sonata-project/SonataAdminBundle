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

namespace Sonata\AdminBundle\Export;

use Sonata\Exporter\Handler;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Sonata\Exporter\Writer\CsvWriter;
use Sonata\Exporter\Writer\JsonWriter;
use Sonata\Exporter\Writer\XlsWriter;
use Sonata\Exporter\Writer\XmlWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

@trigger_error(
    'The '.__NAMESPACE__.'\Exporter class is deprecated since version 3.14 and will be removed in 4.0.'.
    ' Use \Sonata\Exporter\Exporter instead',
    E_USER_DEPRECATED
);

/**
 * NEXT_MAJOR: remove this class.
 *
 * @deprecated since sonata-project/admin-bundle 3.14, to be removed in 4.0.
 */
class Exporter
{
    /**
     * @param string $format
     * @param string $filename
     *
     * @throws \RuntimeException
     *
     * @return StreamedResponse
     */
    public function getResponse($format, $filename, SourceIteratorInterface $source)
    {
        switch ($format) {
            case 'xls':
                $writer = new XlsWriter('php://output');
                $contentType = 'application/vnd.ms-excel';

                break;
            case 'xml':
                $writer = new XmlWriter('php://output');
                $contentType = 'text/xml';

                break;
            case 'json':
                $writer = new JsonWriter('php://output');
                $contentType = 'application/json';

                break;
            case 'csv':
                $writer = new CsvWriter('php://output', ',', '"', '\\', true, true);
                $contentType = 'text/csv';

                break;
            default:
                throw new \RuntimeException('Invalid format');
        }

        $callback = static function () use ($source, $writer) {
            $handler = Handler::create($source, $writer);
            $handler->export();
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}
