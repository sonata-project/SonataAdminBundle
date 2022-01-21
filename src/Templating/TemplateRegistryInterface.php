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

namespace Sonata\AdminBundle\Templating;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Timo Bakx <timobakx@gmail.com>
 */
interface TemplateRegistryInterface
{
    /**
     * @internal
     */
    public const SHOW_TEMPLATES = [
        FieldDescriptionInterface::TYPE_ARRAY => '@SonataAdmin/CRUD/show_array.html.twig',
        FieldDescriptionInterface::TYPE_BOOLEAN => '@SonataAdmin/CRUD/show_boolean.html.twig',
        FieldDescriptionInterface::TYPE_DATE => '@SonataAdmin/CRUD/show_date.html.twig',
        FieldDescriptionInterface::TYPE_TIME => '@SonataAdmin/CRUD/show_time.html.twig',
        FieldDescriptionInterface::TYPE_DATETIME => '@SonataAdmin/CRUD/show_datetime.html.twig',
        FieldDescriptionInterface::TYPE_EMAIL => '@SonataAdmin/CRUD/show_email.html.twig',
        FieldDescriptionInterface::TYPE_ENUM => '@SonataAdmin/CRUD/show_enum.html.twig',
        FieldDescriptionInterface::TYPE_TRANS => '@SonataAdmin/CRUD/show_trans.html.twig',
        FieldDescriptionInterface::TYPE_STRING => '@SonataAdmin/CRUD/base_show_field.html.twig',
        FieldDescriptionInterface::TYPE_INTEGER => '@SonataAdmin/CRUD/base_show_field.html.twig',
        FieldDescriptionInterface::TYPE_FLOAT => '@SonataAdmin/CRUD/base_show_field.html.twig',
        FieldDescriptionInterface::TYPE_CURRENCY => '@SonataAdmin/CRUD/show_currency.html.twig',
        FieldDescriptionInterface::TYPE_PERCENT => '@SonataAdmin/CRUD/show_percent.html.twig',
        FieldDescriptionInterface::TYPE_CHOICE => '@SonataAdmin/CRUD/show_choice.html.twig',
        FieldDescriptionInterface::TYPE_URL => '@SonataAdmin/CRUD/show_url.html.twig',
        FieldDescriptionInterface::TYPE_HTML => '@SonataAdmin/CRUD/show_html.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_MANY => '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_MANY => '@SonataAdmin/CRUD/Association/show_one_to_many.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_ONE => '@SonataAdmin/CRUD/Association/show_one_to_one.html.twig',
    ];

    /**
     * @internal
     */
    public const LIST_TEMPLATES = [
        FieldDescriptionInterface::TYPE_ARRAY => '@SonataAdmin/CRUD/list_array.html.twig',
        FieldDescriptionInterface::TYPE_BOOLEAN => '@SonataAdmin/CRUD/list_boolean.html.twig',
        FieldDescriptionInterface::TYPE_DATE => '@SonataAdmin/CRUD/list_date.html.twig',
        FieldDescriptionInterface::TYPE_TIME => '@SonataAdmin/CRUD/list_time.html.twig',
        FieldDescriptionInterface::TYPE_DATETIME => '@SonataAdmin/CRUD/list_datetime.html.twig',
        FieldDescriptionInterface::TYPE_TEXTAREA => '@SonataAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_EMAIL => '@SonataAdmin/CRUD/list_email.html.twig',
        FieldDescriptionInterface::TYPE_ENUM => '@SonataAdmin/CRUD/list_enum.html.twig',
        FieldDescriptionInterface::TYPE_TRANS => '@SonataAdmin/CRUD/list_trans.html.twig',
        FieldDescriptionInterface::TYPE_STRING => '@SonataAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_INTEGER => '@SonataAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_FLOAT => '@SonataAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_IDENTIFIER => '@SonataAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_CURRENCY => '@SonataAdmin/CRUD/list_currency.html.twig',
        FieldDescriptionInterface::TYPE_PERCENT => '@SonataAdmin/CRUD/list_percent.html.twig',
        FieldDescriptionInterface::TYPE_CHOICE => '@SonataAdmin/CRUD/list_choice.html.twig',
        FieldDescriptionInterface::TYPE_URL => '@SonataAdmin/CRUD/list_url.html.twig',
        FieldDescriptionInterface::TYPE_HTML => '@SonataAdmin/CRUD/list_html.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_MANY => '@SonataAdmin/CRUD/Association/list_many_to_many.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/list_many_to_one.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_MANY => '@SonataAdmin/CRUD/Association/list_one_to_many.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_ONE => '@SonataAdmin/CRUD/Association/list_one_to_one.html.twig',
    ];

    /**
     * @return array<string, string> 'name' => 'file_path.html.twig'
     */
    public function getTemplates(): array;

    public function getTemplate(string $name): string;

    public function hasTemplate(string $name): bool;
}
