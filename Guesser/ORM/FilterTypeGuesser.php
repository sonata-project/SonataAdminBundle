<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Guesser\ORM;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class FilterTypeGuesser implements TypeGuesserInterface
{
    protected $registry;

    private $cache;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->cache = array();
    }

    /**
     * @param string $class
     * @param string $property
     * @return TypeGuess
     */
    function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return false;
        }

        $options = array(
            'field_type'     => false,
            'field_options'  => array(),
            'options'        => array(),
        );

        list($metadata, $name) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            switch ($mapping['type']) {
                case ClassMetadataInfo::ONE_TO_MANY:
                case ClassMetadataInfo::MANY_TO_MANY:
                    $options['field_type'] = 'entity';
                    $options['field_options'] = array(
                        'class' => $class
                    );

                    return new TypeGuess('doctrine_orm_choice', $options, Guess::HIGH_CONFIDENCE);

                case ClassMetadataInfo::MANY_TO_ONE:
                    return new TypeGuess('doctrine_orm_many_to_one', $options, Guess::HIGH_CONFIDENCE);

                case ClassMetadataInfo::ONE_TO_ONE:
                    return new TypeGuess('doctrine_orm_one_to_one', $options, Guess::HIGH_CONFIDENCE);
            }
        }

        switch ($metadata->getTypeOfField($property)) {
            //case 'array':
            //  return new TypeGuess('Collection', $options, Guess::HIGH_CONFIDENCE);
            case 'boolean':
                $options['field_type'] = 'sonata_type_filter_boolean';
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
                $options['field_type'] = 'sonata_type_filter_number';
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

    protected function getMetadata($class)
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $this->cache[$class] = null;
        foreach ($this->registry->getEntityManagers() as $name => $em) {
            try {
                return $this->cache[$class] = array($em->getClassMetadata($class), $name);
            } catch (MappingException $e) {
                // not an entity or mapped super class
            }
        }
    }
}