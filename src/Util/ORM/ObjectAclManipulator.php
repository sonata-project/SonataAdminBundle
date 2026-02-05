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

namespace SensioLabs\AdminBundle\Util\ORM;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Exception\ModelManagerException;
use SensioLabs\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use SensioLabs\AdminBundle\Util\ObjectAclManipulator as BaseObjectAclManipulator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

final class ObjectAclManipulator extends BaseObjectAclManipulator
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function batchConfigureAcls(OutputInterface $output, AdminInterface $admin, ?UserSecurityIdentity $securityIdentity = null): void
    {
        $securityHandler = $admin->getSecurityHandler();
        if (!$securityHandler instanceof AclSecurityHandlerInterface) {
            $output->writeln('Admin class is not configured to use ACL : <info>ignoring</info>');

            return;
        }

        $output->writeln(\sprintf(' > generate ACLs for %s', $admin->getCode()));
        $objectOwnersMsg = null === $securityIdentity ? '' : ' and set the object owner';

        $class = $admin->getClass();
        $em = $this->registry->getManagerForClass($class);
        \assert($em instanceof EntityManager);

        $qb = $em->createQueryBuilder();
        $qb->select('o')->from($class, 'o');

        $count = 0;
        $countUpdated = 0;
        $countAdded = 0;

        try {
            $batchSize = 20;
            $batchSizeOutput = 200;
            $objectIds = [];

            foreach ($qb->getQuery()->toIterable() as $object) {
                $objectIds[] = ObjectIdentity::fromDomainObject($object);

                ++$count;

                if (0 === ($count % $batchSize)) {
                    [$batchAdded, $batchUpdated] = $this->configureAcls($output, $admin, new \ArrayIterator($objectIds), $securityIdentity);
                    $countAdded += $batchAdded;
                    $countUpdated += $batchUpdated;
                    $objectIds = [];
                }

                if (0 === ($count % $batchSizeOutput)) {
                    $output->writeln(\sprintf('   - generated class ACEs%s for %s objects (added %s, updated %s)', $objectOwnersMsg, $count, $countAdded, $countUpdated));
                }
            }

            if (\count($objectIds) > 0) {
                [$batchAdded, $batchUpdated] = $this->configureAcls($output, $admin, new \ArrayIterator($objectIds), $securityIdentity);
                $countAdded += $batchAdded;
                $countUpdated += $batchUpdated;
            }
        } catch (\PDOException|Exception $e) {
            throw new ModelManagerException(
                \sprintf('Failed to configure acl for class: %s', $class),
                (int) $e->getCode(),
                $e
            );
        }

        $output->writeln(\sprintf(
            '   - [TOTAL] generated class ACEs%s for %s objects (added %s, updated %s)',
            $objectOwnersMsg,
            $count,
            $countAdded,
            $countUpdated
        ));
    }
}
