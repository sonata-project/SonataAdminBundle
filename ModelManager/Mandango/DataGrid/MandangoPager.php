<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Mandango\DataGrid;

use Sonata\AdminBundle\Datagrid\Pager;

/**
 * MandangoModelManager.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoPager extends Pager
{
    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->getQuery();

        return $this->getQuery()->getQuery()->execute(array(), $hydrationMode);
    }

    public function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->getQuery()->getQuery();
        }

        return $this->query;
    }

    public function init()
    {
        $this->resetIterator();

        $query = $this->getQuery();
        $this->setNbResults($query->count());

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $query->limit($this->getMaxPerPage())->skip($offset);
        }
    }
}
