<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Datagrid\DatagridInterface;
use SensioLabs\AdminBundle\Util\ParametersManipulator;

/**
 * @author Willem Verspyck <willemverspyck@users.noreply.github.com>
 */
final class ParametersManipulatorTest extends TestCase
{
    /**
     * @phpstan-return iterable<array-key, array{array<mixed>, array<mixed>, array<mixed>}>
     */
    public static function provideMergeParametersCases(): iterable
    {
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
        yield [
            [
                'status' => [
                    'value' => [
                        'foo',
                        'bar',
                    ],
                ],
            ],
            [
                'status' => '',
            ],
            [
                'status' => '',
            ],
        ];
    }

    /**
     * @param mixed[] $parameters
     * @param mixed[] $newParameters
     * @param mixed[] $result
     */
    #[DataProvider('provideMergeParametersCases')]
    public function testMergeParameters(array $parameters, array $newParameters, array $result): void
    {
        static::assertSame($result, ParametersManipulator::merge($parameters, $newParameters));
    }
}
