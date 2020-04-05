<?php

declare(strict_types=1);

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

final class CustomQueryAdmin extends AbstractAdmin
{
    public function createQuery($context = 'list'): ProxyQueryInterface
    {
        /** @var ProxyQueryInterface $query */
        $query = parent::createQuery($context);

        return $query
            ->setSortOrder(Criteria::DESC)
            ->setSortBy([], ['fieldName' => 'updatedAt'])
            ;
    }

    protected function buildList()
    {
        return new FieldDescriptionCollection();
    }
}
