<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Helpers;

/**
 * This is helpers class for supporting old and new PHPUnit versions.
 *
 * @todo NEXT_MAJOR: Remove this class when dropping support for < PHPUnit 5.4
 *
 * @author Oleksandr Savchenko <savchenko.oleksandr.ua@gmail.com>
 */
class PHPUnit_Framework_TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function expectException($exception, $message = '', $code = null)
    {
        if (is_callable('parent::expectException')) {
            parent::expectException($exception);

            if ($message !== '') {
                parent::expectExceptionMessage($message);
            }

            if ($code !== null) {
                parent::expectExceptionCode($code);
            }
        }

        return parent::setExpectedException($exception, $message, $code);
    }

    /**
     * {@inheritdoc}
     */
    protected function createMock($originalClassName)
    {
        if (is_callable('parent::createMock')) {
            return parent::createMock($originalClassName);
        }

        return parent::getMock($originalClassName);
    }
}
