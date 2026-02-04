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

namespace SensioLabs\AdminBundle\Templating;

use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Timo Bakx <timobakx@gmail.com>
 */
interface TemplateRegistryInterface
{
    /**
     * @internal
     */
    public const SHOW_TEMPLATES = [
        FieldDescriptionInterface::TYPE_ARRAY => '@SensioLabsAdmin/CRUD/show_array.html.twig',
        FieldDescriptionInterface::TYPE_BOOLEAN => '@SensioLabsAdmin/CRUD/show_boolean.html.twig',
        FieldDescriptionInterface::TYPE_DATE => '@SensioLabsAdmin/CRUD/show_date.html.twig',
        FieldDescriptionInterface::TYPE_TIME => '@SensioLabsAdmin/CRUD/show_time.html.twig',
        FieldDescriptionInterface::TYPE_DATETIME => '@SensioLabsAdmin/CRUD/show_datetime.html.twig',
        FieldDescriptionInterface::TYPE_EMAIL => '@SensioLabsAdmin/CRUD/show_email.html.twig',
        FieldDescriptionInterface::TYPE_ENUM => '@SensioLabsAdmin/CRUD/show_enum.html.twig',
        FieldDescriptionInterface::TYPE_TRANS => '@SensioLabsAdmin/CRUD/show_trans.html.twig',
        FieldDescriptionInterface::TYPE_STRING => '@SensioLabsAdmin/CRUD/base_show_field.html.twig',
        FieldDescriptionInterface::TYPE_INTEGER => '@SensioLabsAdmin/CRUD/base_show_field.html.twig',
        FieldDescriptionInterface::TYPE_FLOAT => '@SensioLabsAdmin/CRUD/base_show_field.html.twig',
        FieldDescriptionInterface::TYPE_CURRENCY => '@SensioLabsAdmin/CRUD/show_currency.html.twig',
        FieldDescriptionInterface::TYPE_PERCENT => '@SensioLabsAdmin/CRUD/show_percent.html.twig',
        FieldDescriptionInterface::TYPE_CHOICE => '@SensioLabsAdmin/CRUD/show_choice.html.twig',
        FieldDescriptionInterface::TYPE_URL => '@SensioLabsAdmin/CRUD/show_url.html.twig',
        FieldDescriptionInterface::TYPE_HTML => '@SensioLabsAdmin/CRUD/show_html.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_MANY => '@SensioLabsAdmin/CRUD/Association/show_many_to_many.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SensioLabsAdmin/CRUD/Association/show_many_to_one.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_MANY => '@SensioLabsAdmin/CRUD/Association/show_one_to_many.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_ONE => '@SensioLabsAdmin/CRUD/Association/show_one_to_one.html.twig',
    ];

    /**
     * @internal
     */
    public const LIST_TEMPLATES = [
        FieldDescriptionInterface::TYPE_ARRAY => '@SensioLabsAdmin/CRUD/list_array.html.twig',
        FieldDescriptionInterface::TYPE_BOOLEAN => '@SensioLabsAdmin/CRUD/list_boolean.html.twig',
        FieldDescriptionInterface::TYPE_DATE => '@SensioLabsAdmin/CRUD/list_date.html.twig',
        FieldDescriptionInterface::TYPE_TIME => '@SensioLabsAdmin/CRUD/list_time.html.twig',
        FieldDescriptionInterface::TYPE_DATETIME => '@SensioLabsAdmin/CRUD/list_datetime.html.twig',
        FieldDescriptionInterface::TYPE_TEXTAREA => '@SensioLabsAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_EMAIL => '@SensioLabsAdmin/CRUD/list_email.html.twig',
        FieldDescriptionInterface::TYPE_ENUM => '@SensioLabsAdmin/CRUD/list_enum.html.twig',
        FieldDescriptionInterface::TYPE_TRANS => '@SensioLabsAdmin/CRUD/list_trans.html.twig',
        FieldDescriptionInterface::TYPE_STRING => '@SensioLabsAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_INTEGER => '@SensioLabsAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_FLOAT => '@SensioLabsAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_IDENTIFIER => '@SensioLabsAdmin/CRUD/list_string.html.twig',
        FieldDescriptionInterface::TYPE_CURRENCY => '@SensioLabsAdmin/CRUD/list_currency.html.twig',
        FieldDescriptionInterface::TYPE_PERCENT => '@SensioLabsAdmin/CRUD/list_percent.html.twig',
        FieldDescriptionInterface::TYPE_CHOICE => '@SensioLabsAdmin/CRUD/list_choice.html.twig',
        FieldDescriptionInterface::TYPE_URL => '@SensioLabsAdmin/CRUD/list_url.html.twig',
        FieldDescriptionInterface::TYPE_HTML => '@SensioLabsAdmin/CRUD/list_html.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_MANY => '@SensioLabsAdmin/CRUD/Association/list_many_to_many.html.twig',
        FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SensioLabsAdmin/CRUD/Association/list_many_to_one.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_MANY => '@SensioLabsAdmin/CRUD/Association/list_one_to_many.html.twig',
        FieldDescriptionInterface::TYPE_ONE_TO_ONE => '@SensioLabsAdmin/CRUD/Association/list_one_to_one.html.twig',
    ];

    /**
     * @return array<string, string> 'name' => 'file_path.html.twig'
     */
    public function getTemplates(): array;

    public function getTemplate(string $name): string;

    public function hasTemplate(string $name): bool;
}
