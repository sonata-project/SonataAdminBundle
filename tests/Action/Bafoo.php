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

namespace Sonata\AdminBundle\Tests\Action;

class Bafoo
{
    private $dateProp;

    private $datetimeProp;

    /**
     * @return mixed
     */
    public function getDateProp(): ?\DateTime
    {
        return $this->dateProp;
    }

    /**
     * @param mixed $dateProp
     */
    public function setDateProp(\DateTime $dateProp): self
    {
        $this->dateProp = $dateProp;

        return $this;
    }

    public function getDatetimeProp(): \DateTime
    {
        return $this->datetimeProp;
    }

    public function setDatetimeProp(\DateTime $datetimeProp): self
    {
        $this->datetimeProp = $datetimeProp;

        return $this;
    }
}
