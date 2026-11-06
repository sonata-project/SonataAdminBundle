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

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\Util\BCDeprecationParameters;
use Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * NEXT_MAJOR: Remove this file.
 * This file is copied from Symfony since it was added in Symfony 5.1 and we are supporting 4.4.
 *
 * @see https://github.com/symfony/symfony/blob/d39596edaba715d4c7b4bbef8efbb1c6c4e916f0/src/Symfony/Component/DependencyInjection/Compiler/AliasDeprecatedPublicServicesPass.php
 *
 * @internal
 */
final class AliasDeprecatedPublicServicesCompilerPass extends AbstractRecursivePass
{
    public const PRIVATE_TAG_NAME = 'sonata.container.private';

    /**
     * @var array<string, string>
     */
    private $aliases = [];

    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds(self::PRIVATE_TAG_NAME) as $id => $tags) {
            if (!isset($tags[0]['version'])) {
                throw new InvalidArgumentException(sprintf('The "version" attribute is mandatory for the "%s" tag on the "%s" service.', self::PRIVATE_TAG_NAME, $id));
            }

            $definition = $container->getDefinition($id);
            if (!$definition->isPublic() || $definition->isPrivate()) {
                throw new InvalidArgumentException(sprintf('The "%s" service is private: it cannot have the "%s" tag.', $id, self::PRIVATE_TAG_NAME));
            }

            $container
                ->setAlias($id, $aliasId = '.'.self::PRIVATE_TAG_NAME.'.'.$id)
                ->setPublic(true)
                ->setDeprecated(...BCDeprecationParameters::forConfig(
                    'Accessing the "%alias_id%" service directly from the container is deprecated, use dependency injection instead.',
                    $tags[0]['version']
                ));

            $container->setDefinition($aliasId, $definition);

            $this->aliases[$id] = $aliasId;
        }

        parent::process($container);
    }

    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof Reference && isset($this->aliases[$id = (string) $value])) {
            return new Reference($this->aliases[$id], $value->getInvalidBehavior());
        }

        return parent::processValue($value, $isRoot);
    }
}
