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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FakeTemplateRegistryExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_admin_template', [$this, 'getAdminTemplate']),
        ];
    }

    public function getAdminTemplate($name, $adminCode)
    {
        $templates = [
            'base_list_field' => '@SonataAdmin/CRUD/base_list_field.html.twig',
        ];

        if (isset($templates[$name])) {
            return $templates[$name];
        }

        throw new \Exception(sprintf('Template "%s" of Admin "%s" not found in FakeTemplateRegistry', $name, $adminCode));
    }
}
