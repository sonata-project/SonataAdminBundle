<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubFilesystemLoader;

class SonataAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SonataAdminExtension
     */
    private $twigExtension;

    public function setUp()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $pool = new Pool($container, '', '');
        $this->twigExtension = new SonataAdminExtension($pool);

        $loader = new StubFilesystemLoader(array(
            __DIR__.'/../../../Resources/views/CRUD',
        ));

        $environment = new \Twig_Environment($loader, array('strict_variables' => true));
        $environment->addExtension($this->twigExtension);

        $this->twigExtension->initRuntime($environment);
    }

    public function testSlugify()
    {
        $this->assertEquals($this->twigExtension->slugify('test'), 'test');
        $this->assertEquals($this->twigExtension->slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertEquals($this->twigExtension->slugify('Symfony2'), 'symfony2');
        $this->assertEquals($this->twigExtension->slugify('test'), 'test');
        $this->assertEquals($this->twigExtension->slugify('c\'est bientôt l\'été'), 'c-est-bientot-l-ete');
        $this->assertEquals($this->twigExtension->slugify(urldecode('%2Fc\'est+bientôt+l\'été')), 'c-est-bientot-l-ete');
    }

    /**
     * @dataProvider getRenderListElementTests
     */
    public function testRenderListElement($expectedOutput, $type, $value)
    {
        $object = new \stdClass();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $admin->expects($this->any())
                ->method('getTemplate')
                ->will($this->returnValue('SonataAdminBundle:CRUD:base_list_field.html.twig'));

        $admin->expects($this->any())
                ->method('id')
                ->with($this->equalTo($object))
                ->will($this->returnValue(12345));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
                ->method('getAdmin')
                ->will($this->returnValue($admin));

        $fieldDescription->expects($this->any())
                ->method('getValue')
                ->will($this->returnValue($value));

        $fieldDescription->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($type));

        $fieldDescription->expects($this->any())
                ->method('getOptions')
                ->will($this->returnValue(array('currency' => 'EUR')));

        $fieldDescription->expects($this->any())
                ->method('getTemplate')
                ->will($this->returnCallback(
                    function() use ($type) {
                        switch ($type) {
                            case 'string':
                                return 'SonataAdminBundle:CRUD:list_string.html.twig';
                            case 'boolean':
                                return 'SonataAdminBundle:CRUD:list_boolean.html.twig';
                            case 'datetime':
                                return 'SonataAdminBundle:CRUD:list_datetime.html.twig';
                            case 'date':
                                return 'SonataAdminBundle:CRUD:list_date.html.twig';
                            case 'time':
                                return 'SonataAdminBundle:CRUD:list_time.html.twig';
                            case 'currency':
                                return 'SonataAdminBundle:CRUD:list_currency.html.twig';
                            case 'percent':
                                return 'SonataAdminBundle:CRUD:list_percent.html.twig';
                            case 'array':
                                return 'SonataAdminBundle:CRUD:list_array.html.twig';
                            default:
                                return false;
                        }
                    }
        ));


                $this->assertEquals($expectedOutput, trim(preg_replace('/\s+/', ' ', $this->twigExtension->renderListElement($object, $fieldDescription))));
    }

    public function getRenderListElementTests()
    {
        //@todo Add tests for "boolean" and "trans" type

        return array(
            array('<td class="sonata-ba-list-field sonata-ba-list-field-string" objectId="12345"> Example </td>', 'string', 'Example'),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-text" objectId="12345"> Example </td>', 'text', 'Example'),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-textarea" objectId="12345"> Example </td>', 'textarea', 'Example'),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-datetime" objectId="12345"> December 24, 2013 11:11 </td>', 'datetime', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-date" objectId="12345"> December 24, 2013 </td>', 'date', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-time" objectId="12345"> 11:11:12 </td>', 'time', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London'))),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-number" objectId="12345"> 10.746135 </td>', 'number', 10.746135),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-integer" objectId="12345"> 5678 </td>', 'integer', 5678),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-percent" objectId="12345"> 1074.6135 % </td>', 'percent', 10.746135),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-currency" objectId="12345"> EUR 10.746135 </td>', 'currency', 10.746135),
            array('<td class="sonata-ba-list-field sonata-ba-list-field-array" objectId="12345"> [1 => First] [2 => Second] </td>', 'array', array(1 => 'First', 2 => 'Second')),
        );
    }

    /**
     * @dataProvider getRenderViewElementTests
     */
    public function testRenderViewElement($expectedOutput, $type, $value)
    {
        $object = new \stdClass();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $admin->expects($this->any())
                ->method('getTemplate')
                ->will($this->returnValue('SonataAdminBundle:CRUD:base_show_field.html.twig'));

        $admin->expects($this->any())
                ->method('id')
                ->with($this->equalTo($object))
                ->will($this->returnValue(12345));

        $admin->expects($this->any())
                ->method('trans')
                ->will($this->returnCallback(function($id) {
                                    return $id;
                                }));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription->expects($this->any())
                ->method('getAdmin')
                ->will($this->returnValue($admin));

        $fieldDescription->expects($this->any())
                ->method('getValue')
                ->will($this->returnValue($value));

        $fieldDescription->expects($this->any())
                ->method('getLabel')
                ->will($this->returnValue('Data'));

        $fieldDescription->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($type));

        $fieldDescription->expects($this->any())
                ->method('getOptions')
                ->will($this->returnValue(array('currency' => 'EUR', 'safe'     => false)));

        $fieldDescription->expects($this->any())
                ->method('getTemplate')
                ->will($this->returnCallback(
                    function() use ($type) {
                        switch ($type) {
                            case 'boolean':
                                return 'SonataAdminBundle:CRUD:show_boolean.html.twig';
                            case 'datetime':
                                return 'SonataAdminBundle:CRUD:show_datetime.html.twig';
                            case 'date':
                                return 'SonataAdminBundle:CRUD:show_date.html.twig';
                            case 'time':
                                return 'SonataAdminBundle:CRUD:show_time.html.twig';
                            case 'currency':
                                return 'SonataAdminBundle:CRUD:show_currency.html.twig';
                            case 'percent':
                                return 'SonataAdminBundle:CRUD:show_percent.html.twig';
                            case 'array':
                                return 'SonataAdminBundle:CRUD:show_array.html.twig';
                            default:
                                return false;
                        }
                    }
        ));


        $this->assertEquals($expectedOutput, trim(preg_replace('/\s+/', ' ', $this->twigExtension->renderListElement($object, $fieldDescription))));
    }

    public function getRenderViewElementTests()
    {
        //@todo Add tests for "boolean" and "trans" type

        return array(
            array('<th>Data</th> <td>Example</td>', 'string', 'Example'),
            array('<th>Data</th> <td>Example</td>', 'text', 'Example'),
            array('<th>Data</th> <td>Example</td>', 'textarea', 'Example'),
            array('<th>Data</th> <td>December 24, 2013 11:11</td>', 'datetime', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London'))),
            array('<th>Data</th> <td>December 24, 2013</td>', 'date', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London'))),
            array('<th>Data</th> <td>11:11:12</td>', 'time', new \DateTime('2013-12-24 10:11:12', new \DateTimeZone('Europe/London'))),
            array('<th>Data</th> <td>10.746135</td>', 'number', 10.746135),
            array('<th>Data</th> <td>5678</td>', 'integer', 5678),
            array('<th>Data</th> <td> 1074.6135 % </td>', 'percent', 10.746135),
            array('<th>Data</th> <td> EUR 10.746135 </td>', 'currency', 10.746135),
            array('<th>Data</th> <td> [1 => First] [2 => Second] </td>', 'array', array(1 => 'First', 2 => 'Second')),
        );
    }
}
