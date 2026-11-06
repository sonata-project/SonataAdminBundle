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

/**
 * @author Timo Bakx <timobakx@gmail.com>
 *
 * @method bool hasTemplate(string $name)
 */
interface TemplateRegistryInterface
{
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_ARRAY instead.
     */
    public const TYPE_ARRAY = 'array';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_BOOLEAN instead.
     */
    public const TYPE_BOOLEAN = 'boolean';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_DATE instead.
     */
    public const TYPE_DATE = 'date';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_TIME instead.
     */
    public const TYPE_TIME = 'time';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_DATETIME instead.
     */
    public const TYPE_DATETIME = 'datetime';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_STRING instead.
     */
    public const TYPE_TEXT = 'text';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_TEXTAREA instead.
     */
    public const TYPE_TEXTAREA = 'textarea';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_EMAIL instead.
     */
    public const TYPE_EMAIL = 'email';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_TRANS instead.
     */
    public const TYPE_TRANS = 'trans';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_STRING instead.
     */
    public const TYPE_STRING = 'string';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_INTEGER instead.
     */
    public const TYPE_SMALLINT = 'smallint';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_INTEGER instead.
     */
    public const TYPE_BIGINT = 'bigint';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_INTEGER instead.
     */
    public const TYPE_INTEGER = 'integer';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_FLOAT instead.
     */
    public const TYPE_DECIMAL = 'decimal';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_FLOAT instead.
     */
    public const TYPE_FLOAT = 'float';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_IDENTIFIER instead.
     */
    public const TYPE_IDENTIFIER = 'identifier';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_CURRENCY instead.
     */
    public const TYPE_CURRENCY = 'currency';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_PERCENT instead.
     */
    public const TYPE_PERCENT = 'percent';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_CHOICE instead.
     */
    public const TYPE_CHOICE = 'choice';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_URL instead.
     */
    public const TYPE_URL = 'url';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_HTML instead.
     */
    public const TYPE_HTML = 'html';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_MANY_TO_MANY instead.
     */
    public const TYPE_MANY_TO_MANY = 'many_to_many';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_MANY_TO_ONE instead.
     */
    public const TYPE_MANY_TO_ONE = 'many_to_one';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_ONE_TO_MANY instead.
     */
    public const TYPE_ONE_TO_MANY = 'one_to_many';

    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, to be removed in 4.0.
     * use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface::TYPE_ONE_TO_ONE instead.
     */
    public const TYPE_ONE_TO_ONE = 'one_to_one';

    /**
     * @return array<string, string> 'name' => 'file_path.html.twig'
     */
    public function getTemplates();

    /**
     * @param string $name
     *
     * @return string
     */
    public function getTemplate($name);

    // NEXT_MAJOR: Uncomment the following method
    // public function hasTemplate(string $name): bool;
}
