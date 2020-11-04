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
    public const TYPE_ARRAY = 'array';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_DATE = 'date';
    public const TYPE_TIME = 'time';
    public const TYPE_DATETIME = 'datetime';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_STRING instead.
     */
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_EMAIL = 'email';
    public const TYPE_TRANS = 'trans';
    public const TYPE_STRING = 'string';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_INTEGER instead.
     */
    public const TYPE_SMALLINT = 'smallint';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_INTEGER instead.
     */
    public const TYPE_BIGINT = 'bigint';
    public const TYPE_INTEGER = 'integer';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.68, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_FLOAT instead.
     */
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_FLOAT = 'float';
    public const TYPE_IDENTIFIER = 'identifier';
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_PERCENT = 'percent';
    public const TYPE_CHOICE = 'choice';
    public const TYPE_URL = 'url';
    public const TYPE_HTML = 'html';
    public const TYPE_MANY_TO_MANY = 'many_to_many';
    public const TYPE_MANY_TO_ONE = 'many_to_one';
    public const TYPE_ONE_TO_MANY = 'one_to_many';
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
