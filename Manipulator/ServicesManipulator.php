<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Manipulator;

use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
class ServicesManipulator
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $yamlTemplate = '    %s:
        class: %s
        arguments: [~, %s, %s]
        tags:
            - { name: sonata.admin, manager_type: %s, group: admin, label: %s }
';

    private $emptyXmlServiceDefinition = <<<XML
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
    </services>
</container>
XML;

    private $xmlServiceDefinitionTemplate = <<<XML
    <service id="%s" class="%s">
            <argument />
            <argument>%s</argument>
            <argument>%s</argument>

            <tag name="sonata.admin" manager_type="%s" group="admin" label="%s" />
        </service>
        
    </services>
XML;


    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = (string) $file;
    }

    /**
     * @param string $serviceId
     * @param string $modelClass
     * @param string $adminClass
     * @param string $controllerName
     * @param string $managerType
     *
     * @throws \RuntimeException
     */
    public function addResource($serviceId, $modelClass, $adminClass, $controllerName, $managerType)
    {
        $serviceDefinition = '';
        if (is_file($this->file)) {
            $serviceDefinition = trim(file_get_contents($this->file));
        }

        $fileInfo = pathinfo($this->file);

        if ($fileInfo['extension'] == 'xml') {

            if ($serviceDefinition == '') {
                $serviceDefinition = $this->emptyXmlServiceDefinition;
            }
            $serviceDefinition = $this->createServiceDefinitionXmlNode(
                $serviceDefinition,
                $serviceId,
                $modelClass,
                $adminClass,
                $controllerName,
                $managerType
            );

        } else {
            if ($serviceDefinition == '') {
                $serviceDefinition = "services:\n";
            } else {
                $data = (array) Yaml::parse($serviceDefinition);

                if ($serviceDefinition !== '') {
                    $serviceDefinition .= "\n";
                }

                // check if admin-service exists
                if (array_key_exists('services', $data)) {
                    if (array_key_exists($serviceId, (array) $data['services'])) {
                        throw new \RuntimeException(sprintf(
                            'The service "%s" is already defined in the file "%s".',
                            $serviceId,
                            realpath($this->file)
                        ));
                    }

                    if ($data['services'] !== null) {
                        $serviceDefinition .= "\n";
                    }
                } else {
                    $serviceDefinition .= $serviceDefinition === '' ? '' : "\n"."services:\n";
                }
            }

            $serviceDefinition .= sprintf(
                $this->yamlTemplate,
                $serviceId,
                $adminClass,
                $modelClass,
                $controllerName,
                $managerType,
                current(array_slice(explode('\\', $modelClass), -1))
            );
        }

        @mkdir(dirname($this->file), 0777, true);

        if (@file_put_contents($this->file, $serviceDefinition) === false) {
            throw new \RuntimeException(sprintf(
                'Unable to append service "%s" to the file "%s". You will have to do it manually.',
                $serviceId,
                $this->file
            ));
        }
    }

    /**
     * Replaces all service-definition placeholders with the given values and appends it to a existing services definition
     *
     * @param string $servicesContent
     * @param string $serviceId
     * @param string $adminClass
     * @param string $modelClass
     * @param string $controllerName
     * @param string $managerType
     *
     * @return string
     */
    private function createServiceDefinitionXmlNode($servicesContent, $serviceId, $adminClass, $modelClass, $controllerName, $managerType)
    {
        $serviceDefinition = sprintf(
            $this->xmlServiceDefinitionTemplate,
            $serviceId,
            $adminClass,
            $modelClass,
            $controllerName,
            $managerType,
            current(array_slice(explode('\\', $modelClass), -1))
        );

        return str_replace('</services>', $serviceDefinition, $servicesContent);
    }
}
