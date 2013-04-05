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

class SonataAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testSlugify()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $pool = new Pool($container, '','');

        $s = new SonataAdminExtension($pool);

        $this->assertEquals($s->slugify('test'), 'test');
        $this->assertEquals($s->slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertEquals($s->slugify('Symfony2'), 'symfony2');
        $this->assertEquals($s->slugify('test'), 'test');
        $this->assertEquals($s->slugify('c\'est bientôt l\'été'), 'c-est-bientot-l-ete');
        $this->assertEquals($s->slugify(urldecode('%2Fc\'est+bientôt+l\'été')), 'c-est-bientot-l-ete');
    }
}
