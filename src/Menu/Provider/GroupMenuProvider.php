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

namespace Sonata\AdminBundle\Menu\Provider;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher;

// NEXT_MAJOR: Remove the else part when dropping support for knplabs/knp-menu 2.x
if (!method_exists(Matcher::class, 'addVoter')) {
    /**
     * Menu provider based on group options.
     *
     * @final since sonata-project/admin-bundle 3.52
     *
     * @author Alexandru Furculita <alex@furculita.net>
     */
    class GroupMenuProvider extends BaseGroupMenuProvider
    {
        /**
         * Retrieves the menu based on the group options.
         *
         * @throws \InvalidArgumentException if the menu does not exist
         */
        public function get(string $name, array $options = []): ItemInterface
        {
            return $this->doGet($name, $options);
        }

        public function has(string $name, array $options = []): bool
        {
            return $this->doHas($name, $options);
        }
    }
} else {
    /**
     * Menu provider based on group options.
     *
     * @final since sonata-project/admin-bundle 3.52
     *
     * @author Alexandru Furculita <alex@furculita.net>
     */
    class GroupMenuProvider extends BaseGroupMenuProvider
    {
        /**
         * Retrieves the menu based on the group options.
         *
         * @param string $name
         *
         * @throws \InvalidArgumentException if the menu does not exist
         *
         * @return ItemInterface
         */
        public function get($name, array $options = [])
        {
            return $this->doGet($name, $options);
        }

        /**
         * Checks whether a menu exists in this provider.
         *
         * @param string $name
         *
         * @return bool
         */
        public function has($name, array $options = [])
        {
            return $this->doHas($name, $options);
        }
    }
}
