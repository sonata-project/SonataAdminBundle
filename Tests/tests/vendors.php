#!/usr/bin/env php
<?php

set_time_limit(0);

$vendorDir = __DIR__.'/../../vendor';
if (!is_dir($vendorDir)) {
  mkdir($vendorDir);
}

$deps = array(
    array('symfony', 'git://github.com/symfony/symfony.git', isset($_SERVER['SYMFONY_VERSION']) ? $_SERVER['SYMFONY_VERSION'] : 'origin/master'),
    array('knpmenu', 'git://github.com/KnpLabs/KnpMenu.git', 'origin/master'),
<<<<<<< HEAD
    array('twig', 'git://github.com/fabpot/Twig.git', '1.6.2'),
=======
    array('twig', 'git://github.com/fabpot/Twig.git', '1.6.1'),
>>>>>>> 39e78bb31c8324bcd8050080354440aa7fa07414
    array('exporter', 'git://github.com/sonata-project/exporter.git', 'origin/master'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone --quiet %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}
