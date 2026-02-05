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

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\DomCrawler\Crawler;

final class ManyToOneMappingTest extends BasePantherTestCase
{
    public function testCreateEntityWithReferences(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/tests/app/book/create');

        $attributeId = $crawler->filter('.book_id')->attr('name');
        static::assertNotNull($attributeId);
        $attributeName = $crawler->filter('.book_name')->attr('name');
        static::assertNotNull($attributeName);

        $form = $crawler->selectButton('Create')->form();
        $form[$attributeId] = 'book_new_id';
        $form[$attributeName] = 'A wonderful book';

        $crawler->filter('.field-container .sonata-ba-action[title="Add new"]')->click();
        $crawler = $this->client->waitForVisibility('.author_name');

        $authorForm = $this->createAuthorForm($crawler);

        $crawler = $this->client->submit($authorForm);

        /* @phpstan-ignore argument.type */
        $crawler->filter('.book_categories label')->each(static function (Crawler $label): void {
            $label->click();
        });

        $this->client->submit($form);

        self::assertSelectorTextContains('.alert-success', '"A wonderful book" has been successfully created.');

        $this->client->clickLink('Delete');
        $this->client->submitForm('Yes, delete');

        self::assertSelectorTextContains('.alert-success', 'Item "A wonderful book" has been deleted successfully.');
    }

    private function createAuthorForm(Crawler $crawler): Form
    {
        $authorAttributeId = $crawler->filter('.author_id')->attr('name');
        static::assertNotNull($authorAttributeId);
        $authorAttributeName = $crawler->filter('.author_name')->attr('name');
        static::assertNotNull($authorAttributeName);
        $addressAttributeName = $crawler->filter('.author_address')->attr('name');
        static::assertNotNull($addressAttributeName);

        $authorForm = $crawler->filter('.modal-content button[name="btn_create"]')->form();
        $authorForm[$authorAttributeId] = 'Wonderful Id';
        $authorForm[$authorAttributeName] = 'Wonderful Author';
        $authorForm[$addressAttributeName] = 'Wonderful street';

        return $authorForm;
    }
}
