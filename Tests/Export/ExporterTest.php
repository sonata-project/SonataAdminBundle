<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Filter;

use Sonata\AdminBundle\Export\Exporter;
use Exporter\Source\SourceIteratorInterface;
use Exporter\Source\ArraySourceIterator;
use Symfony\Component\HttpFoundation\Response;

class ExporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testFilter()
    {
        $source = $this->getMock('Exporter\Source\SourceIteratorInterface');

        $exporter = new Exporter();
        $exporter->getResponse('foo', 'foo', $source);
    }

    public function testJsonFormat()
    {
        $source = new ArraySourceIterator(array(
            array('foo' => 'bar')
        ));

        $exporter = new Exporter();
        $response = $exporter->getResponse('json', 'foo.json', $source);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }
}
