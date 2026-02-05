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

final class BatchActionsTest extends BaseFunctionalTestCase
{
    #[DataProvider('provideDeleteBatchActionCases')]
    public function testDeleteBatchAction(string $url): void
    {
        $this->client->request(Request::METHOD_GET, $url);
        $this->client->submitForm('OK', [
            'all_elements' => true,
            'action' => 'delete',
        ]);
        $this->client->submitForm('Yes, execute');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('div.alert-success', 'Selected items have been successfully deleted.');
    }

    /**
     * @return iterable<array<string>>
     *
     * @phpstan-return iterable<array{0: string}>
     */
    public static function provideDeleteBatchActionCases(): iterable
    {
        yield 'Normal delete' => ['/admin/tests/app/book/list'];
        yield 'Joined delete' => ['/admin/tests/app/author/list'];
        yield 'More than 20 items delete' => ['/admin/tests/app/sub/list'];
    }
}
