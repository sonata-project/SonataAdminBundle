<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Guesser\ODM\MongoDB;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

class FilterTypeGuesser implements TypeGuesserInterface {

    protected $documentManager;

    public function __construct(DocumentManager $documentManager) {
        $this->documentManager = $documentManager;
    }

    /**
     * @param string $class
     * @param string $property
     * @return TypeGuess
     */
    function guessType($class, $property) {
        if (!$ret = $this->getMetadata($class)) {
            return false;
        }

        $options = array(
            'field_type' => false,
            'field_options' => array(),
            'options' => array(),
        );

        list($metadata, $name) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            switch ($mapping['type']) {
                case ClassMetadataInfo::ONE:
                case ClassMetadataInfo::MANY:
                    //case ClassMetadataInfo::MANY_TO_ONE:
                    //case ClassMetadataInfo::MANY_TO_MANY:

                    $options['operator_type'] = 'sonata_type_boolean';
                    $options['operator_options'] = array();

                    $options['field_type'] = 'entity';
                    $options['field_options'] = array(
                        'class' => $mapping['targetDocument']
                    );
                    $options['field_name'] = $mapping['fieldName'];
                    $options['mapping_type'] = $mapping['type'];

                    return new TypeGuess('doctrine_orm_model', $options, Guess::HIGH_CONFIDENCE);
            }
        }

        $options['field_name'] = $metadata->fieldMappings[$property]['fieldName'];

        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                $options['field_type'] = 'sonata_type_boolean';
                $options['field_options'] = array();

                return new TypeGuess('doctrine_orm_boolean', $options, Guess::HIGH_CONFIDENCE);
//            case 'datetime':
//            case 'vardatetime':
//            case 'datetimetz':
//                return new TypeGuess('doctrine_orm_datetime', $options, Guess::HIGH_CONFIDENCE);
//            case 'date':
//                return new TypeGuess('doctrine_orm_date', $options, Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess('doctrine_orm_number', $options, Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'bigint':
            case 'smallint':
                $options['field_type'] = 'number';
                $options['field_options'] = array(
                    'csrf_protection' => false
                );

                return new TypeGuess('doctrine_orm_number', $options, Guess::MEDIUM_CONFIDENCE);
            case 'string':
            case 'text':
                $options['field_type'] = 'text';

                return new TypeGuess('doctrine_orm_string', $options, Guess::MEDIUM_CONFIDENCE);
            case 'time':
                return new TypeGuess('doctrine_orm_time', $options, Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess('doctrine_orm_string', $options, Guess::LOW_CONFIDENCE);
        }
    }

    protected function getMetadata($class) {
        $this->documentManager->getClassMetadata($class);
    }

}