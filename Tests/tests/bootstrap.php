<?php

/*
 * this file is part of the symfony package.
 *
 * (c) fabien potencier <fabien.potencier@symfony-project.com>
 *
 * for the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// fix encoding issue while running text on different host with different locale configuration
setlocale(LC_ALL, "en_US.UTF-8");

if (file_exists($file = __DIR__.'/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/autoload.php.dist')) {
    require_once $file;
}
