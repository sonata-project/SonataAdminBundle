<?php

namespace Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Admin\AdminInterface;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Model\Message;
use Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle\AdminExtractor;

/**
 * Test for AdminExtractor
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AdminExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdminExtractor
     */
    private $adminExtractor;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminInterface
     */
    private $fooAdmin;

    /**
     * @var AdminInterface
     */
    private $barAdmin;

    public function setUp()
    {
        if (!interface_exists('JMS\TranslationBundle\Translation\ExtractorInterface')) {
            $this->markTestSkipped('JMS Translator Bundle does not exist');
        }
        
        $this->fooAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->barAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        // php 5.3 BC
        $fooAdmin = $this->fooAdmin;
        $barAdmin = $this->barAdmin;

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($id) use ($fooAdmin, $barAdmin) {
                switch ($id) {
                    case 'foo_admin':
                        return $fooAdmin;
                    case 'bar_admin':
                        return $barAdmin;
                }

                return null;
            }));

        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');

        $this->pool = new Pool($container, '', '');
        $this->pool->setAdminServiceIds(array('foo_admin', 'bar_admin'));

        $this->adminExtractor = new AdminExtractor($this->pool, $logger);
        $this->adminExtractor->setLogger($logger);
    }

    public function testExtractEmpty()
    {
        $catalogue = $this->adminExtractor->extract();

        $this->assertInstanceOf('JMS\TranslationBundle\Model\MessageCatalogue', $catalogue);
        $this->assertFalse($catalogue->has(new Message('foo', 'foo_admin_domain')));
    }

    public function testExtract()
    {
        // php 5.3 BC
        $translator = $this->adminExtractor;

        $this->fooAdmin->expects($this->any())
            ->method('getShow')
            ->will($this->returnCallback(function() use ($translator) {
                $translator->trans('foo', array(), 'foo_admin_domain');
                $translator->transChoice('foo', 1, array(), 'foo_admin_domain');

                return null;
            }));

        $catalogue = $this->adminExtractor->extract();

        $this->assertTrue($catalogue->has(new Message('foo', 'foo_admin_domain')));
        $this->assertFalse($catalogue->has(new Message('nonexistent', 'foo_admin_domain')));

        $this->assertInstanceOf('JMS\TranslationBundle\Model\Message', $catalogue->get('foo', 'foo_admin_domain'));

        $message = $catalogue->get('foo', 'foo_admin_domain');
        $this->assertEquals('foo', $message->getId());
        $this->assertEquals('foo_admin_domain', $message->getDomain());
    }

    public function testExtractWithException()
    {
        $this->setExpectedException('RuntimeException', 'Foo throws exception');

        $this->fooAdmin->expects($this->any())
            ->method('getShow')
            ->will($this->returnCallback(function() {
                throw new \RuntimeException('Foo throws exception');
            }));

        $catalogue = $this->adminExtractor->extract();
    }
}
