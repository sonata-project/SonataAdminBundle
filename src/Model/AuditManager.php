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

namespace Sonata\AdminBundle\Model;

use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AuditManager implements AuditManagerInterface
{
    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var array
     */
    protected $readers = [];

    /**
     * @var SymfonyContainerInterface
     */
    protected $container;

    /**
     * @var ContainerInterface|null
     */
    private $psrContainer;

    // NEXT_MAJOR: Remove SymfonyContainerInterface parameter and only use ContainerInterface parameter
    public function __construct(SymfonyContainerInterface $container, ?ContainerInterface $psrContainer = null)
    {
        $this->container = $container;
        $this->psrContainer = $psrContainer;
    }

    public function setReader($serviceId, array $classes)
    {
        // NEXT_MAJOR: Remove this "if" block.
        if (null !== $this->psrContainer && !$this->psrContainer->has($serviceId)) {
            @trigger_error(sprintf(
                'Not registering the audit reader "%1$s" with tag "%2$s" is deprecated since'
                .' sonata-project/admin-bundle 3.x and will not work in 4.0.'
                .' You MUST add "%2$s" tag to the service "%1$s".',
                $serviceId,
                AddAuditReadersCompilerPass::AUDIT_READER_TAG
            ), \E_USER_DEPRECATED);
        }

        $this->readers[$serviceId] = $classes;
    }

    public function hasReader($class)
    {
        foreach ($this->readers as $classes) {
            if (\in_array($class, $classes, true)) {
                return true;
            }
        }

        return false;
    }

    public function getReader($class)
    {
        foreach ($this->readers as $readerId => $classes) {
            if (\in_array($class, $classes, true)) {
                return $this->container->get($readerId);
            }
        }

        // NEXT_MAJOR: Throw a \LogicException instead.
        throw new \RuntimeException(sprintf('The class "%s" does not have any reader manager', $class));
    }
}
