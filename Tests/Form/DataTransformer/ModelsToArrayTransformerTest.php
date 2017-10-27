<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;

class ModelsToArrayTransformerTest extends TestCase
{
    private $modelManager;

    protected function setUp()
    {
        $this->modelManager = $this->prophesize('Sonata\AdminBundle\Model\ModelManagerInterface')->reveal();
    }

    public function testConstructor()
    {
        $transformer = new ModelsToArrayTransformer(
            $this->modelManager,
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo'
        );

        $this->assertInstanceOf('Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer', $transformer);
    }

    /**
     * @group legacy
     */
    public function testLegacyConstructor()
    {
        $choiceListClass = 'Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader';

        $choiceList = $this->prophesize($choiceListClass)->reveal();

        $transformer = new ModelsToArrayTransformer(
            $choiceList,
            $this->modelManager,
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo'
        );

        $this->assertInstanceOf('Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer', $transformer);
    }
}
