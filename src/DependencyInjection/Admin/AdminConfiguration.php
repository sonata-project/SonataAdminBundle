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

namespace Sonata\AdminBundle\DependencyInjection\Admin;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;


/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"}) 
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AdminConfiguration
{
    public $class;
    public $controller;
    public $label;
    public $showInDashbaord = true;
    public $group = 'admin';
    public $labelCatalogue;
    public $icon;
    public $onTop = false;
    public $keepOpen = false;
    public $managerType = null;

    public function __construct(
        string $class = null,
        string $controller = null,
        string $label = null,
        bool $showInDashbaord = null,
        string $group = null,
        string $labelCatalogue = null,
        string $icon = null,
        bool $onTop = null,
        bool $keepOpen = null,
        string $managerType = null
    ) {
        $this->class = $class;
        $this->controller = $controller;
        $this->label = $label;
        $this->showInDashbaord = $showInDashbaord !== null ? $showInDashbaord : $this->showInDashbaord;
        $this->group = $group !== null ? $group : $this->group;
        $this->labelCatalogue = $labelCatalogue;
        $this->icon = $icon;
        $this->onTop = $onTop !== null ? $onTop : $this->onTop;
        $this->keepOpen = $keepOpen !== null ? $keepOpen : $this->keepOpen;
        $this->managerType = $managerType;
    }
}
