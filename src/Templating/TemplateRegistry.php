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
 */
final class TemplateRegistry implements MutableTemplateRegistryInterface
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_DATE = 'date';
    public const TYPE_TIME = 'time';
    public const TYPE_DATETIME = 'datetime';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_STRING instead.
     */
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_EMAIL = 'email';
    public const TYPE_TRANS = 'trans';
    public const TYPE_STRING = 'string';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_INTEGER instead.
     */
    public const TYPE_SMALLINT = 'smallint';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_INTEGER instead.
     */
    public const TYPE_BIGINT = 'bigint';
    public const TYPE_INTEGER = 'integer';
    /**
     * NEXT_MAJOR: Remove this constant.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0. Use Sonata\AdminBundle\Templating\TemplateRegistry::TYPE_FLOAT instead.
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
     * @var string[]
     */
    private $templates = [];

    /**
     * @param string[] $templates
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }

    /**
     * @return string[]
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * @param string[] $templates
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    public function hasTemplate(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    /**
     * @param string $name
     */
    public function getTemplate($name): ?string
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        @trigger_error(sprintf(
            'Passing a nonexistent template name as argument 1 to %s() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare string as return type
        // throw new \InvalidArgumentException(sprintf(
        //    'Template named "%s" doesn\'t exist.',
        //    $name
        // ));

        return null;
    }

    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }
}
