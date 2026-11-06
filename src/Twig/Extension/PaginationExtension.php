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

namespace Sonata\AdminBundle\Twig\Extension;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @internal
 */
final class PaginationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_pagination_parameters', [$this, 'getPaginationParameters']),
            new TwigFunction('sonata_sort_parameters', [$this, 'getSortParameters']),
        ];
    }

    public function getPaginationParameters(AdminInterface $admin, int $page): array
    {
        if (method_exists($admin->getDatagrid(), 'getPaginationParameters')) {
            return $admin->getDatagrid()->getPaginationParameters($page);
        }

        return $admin->getModelManager()->getPaginationParameters($admin->getDatagrid(), $page);
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription, AdminInterface $admin): array
    {
        if (method_exists($admin->getDatagrid(), 'getSortParameters')) {
            return $admin->getDatagrid()->getSortParameters($fieldDescription);
        }

        return $admin->getModelManager()->getSortParameters($fieldDescription, $admin->getDatagrid());
    }
}
