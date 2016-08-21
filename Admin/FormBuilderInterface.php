<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Symfony\Component\Form\FormInterface;

/**
 * Builds admin forms. There is a dependency on the AdminInterface because
 * this object holds useful object to deal with this task, but there is
 * probably a better design.
 *
 * @author Christian Gripp <mail@core23.de>
 */
interface FormBuilderInterface
{
    /**
     * @param AdminInterface $admin
     *
     * @return FormInterface
     */
    public function getCreateForm(AdminInterface $admin);

    /**
     * @param AdminInterface $admin
     *
     * @return FormInterface
     */
    public function getEditForm(AdminInterface $admin);

    /**
     * @param AdminInterface $admin
     *
     * @return FormInterface
     */
    public function getShowForm(AdminInterface $admin);
}
