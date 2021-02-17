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

namespace Sonata\AdminBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Util\ParametersManipulator;

/**
 * @author Willem Verspyck
 */
class ParametersManipulatorTest extends TestCase
{
    /**
     * @return array
     */
    public function provideMergeParameters(): array
    {
        return [
            [
                [
                    '_sort_order' => 'DESC',
                    '_sort_by' => 'id',
                    'status' => [
                        'type' => '1',
                        'value' => 'foo',
                    ],
                ],
                [
                    'status' => [
                        'type' => '2',
                        'value' => 'foo',
                    ],
                ],
                [
                    '_sort_order' => 'DESC',
                    '_sort_by' => 'id',
                    'status' => [
                        'type' => '2',
                        'value' => 'foo',
                    ],
                ],
            ],
            [
                [
                    'status' => [
                        'type' => '1',
                    ],
                ],
                [
                    'status' => [
                        'value' => 'foo',
                    ],
                ],
                [
                    'status' => [
                        'type' => '1',
                        'value' => 'foo',
                    ],
                ],
            ],
            [
                [
                    'status' => [
                        'type' => '1',
                        'value' => 'foo',
                    ],
                ],
                [
                    'status' => [
                        'type' => '2',
                    ],
                    '_page' => 2,
                    '_per_page' => 25,
                ],
                [
                    'status' => [
                        'type' => '2',
                        'value' => 'foo',
                    ],
                    '_page' => 2,
                    '_per_page' => 25,
                ],
            ],
            [
                [
                    'status' => [
                        'type' => '1',
                        'value' => [
                            'foo',
                            'bar',
                        ],
                    ],
                ],
                [
                    'status' => [
                        'value' => [
                            'foo',
                        ],
                    ],
                ],
                [
                    'status' => [
                        'type' => '1',
                        'value' => [
                            'foo',
                        ],
                    ],
                ],
            ],
            [
                [
                    'status' => [
                        'value' => [
                            'foo',
                            'bar',
                        ],
                    ],
                ],
                [
                    'status' => [
                        'value' => [
                            'baz',
                        ],
                    ],
                ],
                [
                    'status' => [
                        'value' => [
                            'baz',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideMergeParameters
     *
     * @param array $parameters
     * @param array $filters
     * @param array $result
     */
    public function testMergeParameters(array $parameters, array $filters, array $result): void
    {
        $this->assertSame($result, ParametersManipulator::merge($parameters, $filters));
    }
}
