<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Action;

final class Bafoo
{
    private ?\DateTime $dateProp = null;

    private ?\DateTime $datetimeProp = null;

    public function getDateProp(): ?\DateTime
    {
        return $this->dateProp;
    }

    public function setDateProp(\DateTime $dateProp): static
    {
        $this->dateProp = $dateProp;

        return $this;
    }

    public function getDatetimeProp(): ?\DateTime
    {
        return $this->datetimeProp;
    }

    public function setDatetimeProp(\DateTime $datetimeProp): static
    {
        $this->datetimeProp = $datetimeProp;

        return $this;
    }
}
