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
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Util\ParametersManipulator;

/**
 * @author Willem Verspyck <willemverspyck@users.noreply.github.com>
 */
class ParametersManipulatorTest extends TestCase
{
    /**
     * @phpstan-return iterable<array-key, array{array<mixed>, array<mixed>, array<mixed>}>
     */
    public function provideMergeParameters(): iterable
    {
        return [
            [
                [
                    DatagridInterface::SORT_ORDER => 'DESC',
                    DatagridInterface::SORT_BY => 'id',
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
                    DatagridInterface::SORT_ORDER => 'DESC',
                    DatagridInterface::SORT_BY => 'id',
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
                    DatagridInterface::PAGE => 2,
                    DatagridInterface::PER_PAGE => 25,
                ],
                [
                    'status' => [
                        'type' => '2',
                        'value' => 'foo',
                    ],
                    DatagridInterface::PAGE => 2,
                    DatagridInterface::PER_PAGE => 25,
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
     * @param mixed[] $parameters
     * @param mixed[] $newParameters
     * @param mixed[] $result
     *
     * @dataProvider provideMergeParameters
     */
    public function testMergeParameters(array $parameters, array $newParameters, array $result): void
    {
        $this->assertSame($result, ParametersManipulator::merge($parameters, $newParameters));
    }
}
