<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Builder;

use Sonata\AdminBundle\Tests\Fixtures\Admin\FieldDescription;
use Sonata\AdminBundle\Tests\Fixtures\Builder\FakeListBuilder;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AbstractListBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildActionFieldDescription()
    {
        $fieldDescription = new FieldDescription();
        $fieldDescription->setOptions(array(
            'actions' => array(
                'show' => array(),
            ),
        ));

        $listBuilder = new FakeListBuilder();
        $listBuilder->buildActionFieldDescription($fieldDescription);

        $this->assertSame('SonataAdminBundle:CRUD:list__action.html.twig', $fieldDescription->getTemplate());
        $this->assertSame('action', $fieldDescription->getType());
        $this->assertSame('Action', $fieldDescription->getOption('name'));
        $this->assertSame('Action', $fieldDescription->getOption('code'));
        $this->assertSame(array(
            'show' => array(
                'template' => 'SonataAdminBundle:CRUD:list__action_show.html.twig',
            ),
        ), $fieldDescription->getOption('actions'));
    }
}
