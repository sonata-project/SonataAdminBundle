<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Resources;


use Sonata\CoreBundle\Test\XliffValidatorTestCase;

class XliffTest extends XliffValidatorTestCase
{
    /**
     * @return array List all path to validate xliff
     */
    public function getXliffPaths()
    {
        return array(array(__DIR__.'/../../Resources/translations'));
    }
}
