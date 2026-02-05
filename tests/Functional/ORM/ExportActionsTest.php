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

namespace SensioLabs\AdminBundle\Tests\Functional\ORM;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class ExportActionsTest extends BaseFunctionalTestCase
{
    /**
     * @param array<mixed> $parameters
     * @param array<mixed> $expected
     */
    #[DataProvider('provideExportActionCases')]
    public function testExportAction(string $url, array $parameters, array $expected): void
    {
        $this->client->request(Request::METHOD_GET, $url, $parameters);

        self::assertResponseIsSuccessful();
        static::assertSame(
            $expected,
            json_decode(
                $this->client->getInternalResponse()->getContent(),
                true,
                flags: \JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * @return iterable<array<string>>
     *
     * @phpstan-return iterable<array{0: string, 1: array<mixed>, 2: array<mixed>}>
     */
    public static function provideExportActionCases(): iterable
    {
        yield 'Normal export' => ['/admin/tests/app/book/export', [
            'format' => 'json',
        ], [
            ['Id' => 'book_1', 'Name' => 'Book 1'],
            ['Id' => 'book_2', 'Name' => 'Book 2'],
            ['Id' => 'book_id', 'Name' => 'Don Quixote'],
        ]];

        yield 'Normal export with normal sort' => ['/admin/tests/app/book/export', [
            'format' => 'json',
            'filter' => [
                '_sort_by' => 'name',
                '_sort_order' => 'DESC',
            ],
        ], [
            ['Id' => 'book_id', 'Name' => 'Don Quixote'],
            ['Id' => 'book_2', 'Name' => 'Book 2'],
            ['Id' => 'book_1', 'Name' => 'Book 1'],
        ]];

        yield 'Joined export' => ['/admin/tests/app/author/export', [
            'format' => 'json',
        ], [
            ['Id' => 'anonymous', 'Name' => 'Anonymous', 'Address Street' => ''],
            ['Id' => 'author_with_two_books', 'Name' => 'Author with 2 books', 'Address Street' => ''],
            ['Id' => 'autocompletion_author', 'Name' => 'autocompletion author', 'Address Street' => ''],
            ['Id' => 'miguel_de_cervantes', 'Name' => 'Miguel de Cervantes', 'Address Street' => 'Somewhere in La Mancha, in a place whose name I do not care to remember'],
        ]];

        yield 'Joined export with normal sort' => ['/admin/tests/app/author/export', [
            'format' => 'json',
            'filter' => [
                '_sort_by' => 'id',
                '_sort_order' => 'DESC',
            ],
        ], [
            ['Id' => 'miguel_de_cervantes', 'Name' => 'Miguel de Cervantes', 'Address Street' => 'Somewhere in La Mancha, in a place whose name I do not care to remember'],
            ['Id' => 'autocompletion_author', 'Name' => 'autocompletion author', 'Address Street' => ''],
            ['Id' => 'author_with_two_books', 'Name' => 'Author with 2 books', 'Address Street' => ''],
            ['Id' => 'anonymous', 'Name' => 'Anonymous', 'Address Street' => ''],
        ]];

        yield 'Joined export with oneToMany sort' => ['/admin/tests/app/author/export', [
            'format' => 'json',
            'filter' => [
                '_sort_by' => 'firstBook',
                '_sort_order' => 'DESC',
            ],
        ], [
            ['Id' => 'miguel_de_cervantes', 'Name' => 'Miguel de Cervantes', 'Address Street' => 'Somewhere in La Mancha, in a place whose name I do not care to remember'],
            ['Id' => 'autocompletion_author', 'Name' => 'autocompletion author', 'Address Street' => ''],
            ['Id' => 'author_with_two_books', 'Name' => 'Author with 2 books', 'Address Street' => ''],
            ['Id' => 'anonymous', 'Name' => 'Anonymous', 'Address Street' => ''],
        ]];

        yield 'Joined export with ManyToOne sort' => ['/admin/tests/app/item/export', [
            'format' => 'json',
            'filter' => [
                '_sort_by' => 'product.name',
                '_sort_order' => 'DESC',
            ],
        ], [
            ['Offered Price' => '3'],
            ['Offered Price' => '2'],
            ['Offered Price' => '2'],
        ]];
    }
}
