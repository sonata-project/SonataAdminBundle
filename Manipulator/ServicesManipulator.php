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

    private $xmlTemplate = '
        <service id="%s" class="%s">
            <argument />
            <argument>%s</argument>
            <argument>%s</argument>

            <tag name="sonata.admin" manager_type="%s" group="%s" label="%s" />
        </service>    
';

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
        if (preg_match('/\.xml/', $this->file) !== 0) {
//            $dom = new \DOMDocument();
//            $dom->preserveWhiteSpace = false;
//            $dom->formatOutput = true;
//            $dom->load($this->file);
//
//            $servicesTag = $dom->childNodes->item(0)->childNodes->item(0);
//
//            $serviceTag = $this->createServiceDefinitionXmlNode(
//                $dom,
//                $serviceId,
//                $modelClass,
//                $adminClass,
//                $controllerName,
//                $managerType
//            );
//
//            $lineNode = $dom->createElement('empty', '\n');
//            $servicesTag->appendChild($lineNode);
//            $servicesTag->appendChild($serviceTag);
//
//            $servicesTag->removeChild($lineNode);
//
//            $xml = str_replace("  ", "    ", $dom->saveXML());

            $servicesContent = file_get_contents($this->file);
            $servicesContent = $this->createServiceDefinitionXmlNode(
                $servicesContent,
                $serviceId,
                $modelClass,
                $adminClass,
                $controllerName,
                $managerType
            );
            file_put_contents($this->file, $servicesContent);

            return;
        }

        $code = "services:\n";

        if (is_file($this->file)) {
            $code = rtrim(file_get_contents($this->file));
            $data = (array) Yaml::parse($code);

            if ($code !== '') {
                $code .= "\n";
            }

            if (array_key_exists('services', $data)) {
                if (array_key_exists($serviceId, (array) $data['services'])) {
                    throw new \RuntimeException(sprintf(
                        'The service "%s" is already defined in the file "%s".',
                        $serviceId,
                        realpath($this->file)
                    ));
                }

                if ($data['services'] !== null) {
                    $code .= "\n";
                }
            } else {
                $code .= $code === '' ? '' : "\n"."services:\n";
            }
        }

        $code .= sprintf(
            $this->yamlTemplate,
            $serviceId,
            $adminClass,
            $modelClass,
            $controllerName,
            $managerType,
            current(array_slice(explode('\\', $modelClass), -1))
        );
        @mkdir(dirname($this->file), 0777, true);

        if (@file_put_contents($this->file, $code) === false) {
            throw new \RuntimeException(sprintf(
                'Unable to append service "%s" to the file "%s". You will have to do it manually.',
                $serviceId,
                $this->file
            ));
        }
    }

    private function createServiceDefinitionXmlNode($servicesContent, $serviceId, $adminClass, $modelClass, $controllerName, $managerType)
    {
$template = <<<XML
    <service id="%s" class="%s">
            <argument />
            <argument>%s</argument>
            <argument>%s</argument>

            <tag name="sonata.admin" manager_type="%s" group="admin" label="%s" />
        </service>
        
    </services>
XML;

        $template = sprintf($template, $serviceId, $adminClass, $modelClass, $controllerName, $managerType, current(array_slice(explode('\\', $modelClass), -1)));

        $content = str_replace('</services>', $template, $servicesContent);

//        $serviceElement = $dom->createElement('service');
//        $serviceElement->setAttribute('id', $serviceId);
//        $serviceElement->setAttribute('class', $adminClass);
//
//        $argumentElement1 = $dom->createElement('argument');
//        $argumentElement2 = $dom->createElement('argument');
//        $argumentElement2->nodeValue = $modelClass;
//        $argumentElement3 = $dom->createElement('argument');
//        $argumentElement3->nodeValue = $controllerName;
//
//        $tagElement = $dom->createElement('tag');
//        $tagElement->setAttribute('name', 'sonata.admin');
//        $tagElement->setAttribute('manager_type', $managerType);
//        $tagElement->setAttribute('group', 'admin');
//        $tagElement->setAttribute('label', current(array_slice(explode('\\', $modelClass), -1)));
//
//        $serviceElement->appendChild($argumentElement1);
//        $serviceElement->appendChild($argumentElement2);
//        $serviceElement->appendChild($argumentElement3);
//        $serviceElement->appendChild($tagElement);

        return $content;



    }
}
