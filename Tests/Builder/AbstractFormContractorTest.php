<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Builder;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class AbstractFormContractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $formType
     * @param string $targetEntity
     * @param string $associationType
     * @param string $associationAdmin
     * @param string $expectedException
     *
     * @dataProvider testFieldDescriptionValidationProvider
     */
    public function testFieldDescriptionValidation($formType, $targetEntity, $associationType, $associationAdmin, $expectedException)
    {
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\AdminInterface')->getMock();
        $admin->method('getClass')->willReturn('Foo');

        $fieldDescription = $this->getMockBuilder('Sonata\AdminBundle\Admin\FieldDescriptionInterface')->getMock();
        $fieldDescription->method('getAdmin')->willReturn($admin);
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);
        $fieldDescription->method('getTargetEntity')->willReturn($targetEntity);
        $isSingleAssociation = $associationType === 'single';
        $fieldDescription->method('describesSingleValuedAssociation')->willReturn($isSingleAssociation);
        $fieldDescription->method('describesCollectionValuedAssociation')->willReturn(!$isSingleAssociation);

        $formContractor = $this->getMockForAbstractClass(
            'Sonata\AdminBundle\Builder\AbstractFormContractor',
            array(),
            '',
            false
        );

        $this->setExpectedException($expectedException);
        $formContractor->getDefaultOptions($formType, $fieldDescription);
    }

    public function testFieldDescriptionValidationProvider()
    {
        return array(
            // MissingTargetModelClassException
            'AdminType, no target entity' => array(
                'Sonata\AdminBundle\Form\Type\AdminType',
                null,
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            'CollectionType, no target entity' => array(
                'Sonata\CoreBundle\Form\Type\CollectionType',
                null,
                'collection',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            'ModelType, no target entity' => array(
                'Sonata\AdminBundle\Form\Type\ModelType',
                null,
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            'ModelTypeList, no target entity' => array(
                'Sonata\AdminBundle\Form\Type\ModelTypeList',
                null,
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            'ModelHiddenType, no target entity' => array(
                'Sonata\AdminBundle\Form\Type\ModelHiddenType',
                null,
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            'ModelReferenceType, no target entity' => array(
                'Sonata\AdminBundle\Form\Type\ModelReferenceType',
                null,
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            'ModelAutocompleteType, no target entity' => array(
                'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
                null,
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException',
            ),
            // UnhandledAssociationTypeException
            'AdminType, collection-valued association' => array(
                'Sonata\AdminBundle\Form\Type\AdminType',
                'Foo',
                'collection',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException',
            ),
            'ModelTypeList, collection-valued association' => array(
                'Sonata\AdminBundle\Form\Type\ModelTypeList',
                'Foo',
                'collection',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException',
            ),
            'ModelHiddenType, collection-valued association' => array(
                'Sonata\AdminBundle\Form\Type\ModelHiddenType',
                'Foo',
                'collection',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException',
            ),
            'ModelReferenceType, collection-valued association' => array(
                'Sonata\AdminBundle\Form\Type\ModelReferenceType',
                'Foo',
                'collection',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException',
            ),
            'CollectionType, singled-valued association' => array(
                'Sonata\CoreBundle\Form\Type\CollectionType',
                'Foo',
                'single',
                'FooAdmin',
                'Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException',
            ),
            // MissingAssociationAdminException
            'AdminType, no associationAdmin' => array(
                'Sonata\AdminBundle\Form\Type\AdminType',
                'Foo',
                'single',
                null,
                'Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException',
            ),
            'CollectionType, no associationAdmin' => array(
                'Sonata\CoreBundle\Form\Type\CollectionType',
                'Foo',
                'collection',
                null,
                'Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException',
            ),
            'ModelTypeList, no associationAdmin' => array(
                'Sonata\AdminBundle\Form\Type\ModelTypeList',
                'Foo',
                'single',
                null,
                'Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException',
            ),
            'ModelAutocompleteType, no associationAdmin' => array(
                'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
                'Foo',
                'single',
                null,
                'Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException',
            ),
        );
    }
}
