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

namespace Sonata\AdminBundle\Tests\App\Builder;

use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class ShowBuilder implements ShowBuilderInterface
{
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));
        }
    }

    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        return new FieldDescriptionCollection();
    }

    public function addField(FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setType($type);
        $this->fixFieldDescription($fieldDescription);

        $list->add($fieldDescription);
    }

    private function getTemplate(?string $type): ?string
    {
        switch ($type) {
            case FieldDescriptionInterface::TYPE_ARRAY:
                return '@SonataAdmin/CRUD/show_array.html.twig';
            case FieldDescriptionInterface::TYPE_BOOLEAN:
                return '@SonataAdmin/CRUD/show_boolean.html.twig';
            case FieldDescriptionInterface::TYPE_DATE:
                return '@SonataAdmin/CRUD/show_date.html.twig';
            case FieldDescriptionInterface::TYPE_TIME:
                return '@SonataAdmin/CRUD/show_time.html.twig';
            case FieldDescriptionInterface::TYPE_DATETIME:
                return '@SonataAdmin/CRUD/show_datetime.html.twig';
            case FieldDescriptionInterface::TYPE_TEXTAREA:
            case FieldDescriptionInterface::TYPE_STRING:
            case FieldDescriptionInterface::TYPE_INTEGER:
            case FieldDescriptionInterface::TYPE_FLOAT:
            case FieldDescriptionInterface::TYPE_IDENTIFIER:
                return '@SonataAdmin/CRUD/base_show_field.html.twig';
            case FieldDescriptionInterface::TYPE_EMAIL:
                return '@SonataAdmin/CRUD/show_email.html.twig';
            case FieldDescriptionInterface::TYPE_TRANS:
                return '@SonataAdmin/CRUD/show_trans.html.twig';
            case FieldDescriptionInterface::TYPE_CURRENCY:
                return '@SonataAdmin/CRUD/show_currency.html.twig';
            case FieldDescriptionInterface::TYPE_PERCENT:
                return '@SonataAdmin/CRUD/show_percent.html.twig';
            case FieldDescriptionInterface::TYPE_CHOICE:
                return '@SonataAdmin/CRUD/show_choice.html.twig';
            case FieldDescriptionInterface::TYPE_URL:
                return '@SonataAdmin/CRUD/show_url.html.twig';
            case FieldDescriptionInterface::TYPE_HTML:
                return '@SonataAdmin/CRUD/show_html.html.twig';
            case FieldDescriptionInterface::TYPE_MANY_TO_MANY:
                return '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig';
            case FieldDescriptionInterface::TYPE_MANY_TO_ONE:
                return '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig';
            case FieldDescriptionInterface::TYPE_ONE_TO_MANY:
                return '@SonataAdmin/CRUD/Association/show_one_to_many.html.twig';
            case FieldDescriptionInterface::TYPE_ONE_TO_ONE:
                return '@SonataAdmin/CRUD/Association/show_one_to_one.html.twig';
            default:
                return null;
        }
    }
}
