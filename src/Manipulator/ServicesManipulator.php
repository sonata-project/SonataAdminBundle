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

namespace Sonata\AdminBundle\Manipulator;

use Symfony\Component\Yaml\Yaml;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
final class ServicesManipulator
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $template = '    %s:
        class: %s
        arguments: [~, %s, %s]
        tags:
            - { name: sonata.admin, manager_type: %s, group: admin, label: %s }
        public: true
';

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @phpstan-param class-string $modelClass
     * @phpstan-param class-string $adminClass
     *
     * @throws \RuntimeException
     */
    public function addResource(string $serviceId, string $modelClass, string $adminClass, string $controllerName, string $managerType): void
    {
        $code = "services:\n";

        if (is_file($this->file)) {
            $content = file_get_contents($this->file);
            if (false === $content) {
                throw new \RuntimeException(sprintf('Cannot read the file "%s".', realpath($this->file)));
            }

            $code = rtrim($content);
            $data = (array) Yaml::parse($code);

            if ('' !== $code) {
                $code .= "\n";
            }

            if (\array_key_exists('services', $data)) {
                if (\array_key_exists($serviceId, (array) $data['services'])) {
                    throw new \RuntimeException(sprintf(
                        'The service "%s" is already defined in the file "%s".',
                        $serviceId,
                        realpath($this->file)
                    ));
                }

                if (null !== $data['services']) {
                    $code .= "\n";
                }
            } else {
                $code .= '' === $code ? '' : "\n"."services:\n";
            }
        }

        $code .= sprintf(
            $this->template,
            $serviceId,
            $adminClass,
            $modelClass,
            $controllerName,
            $managerType,
            current(\array_slice(explode('\\', $modelClass), -1))
        );
        @mkdir(\dirname($this->file), 0777, true);

        if (false === @file_put_contents($this->file, $code)) {
            throw new \RuntimeException(sprintf(
                'Unable to append service "%s" to the file "%s". You will have to do it manually.',
                $serviceId,
                $this->file
            ));
        }
    }
}
