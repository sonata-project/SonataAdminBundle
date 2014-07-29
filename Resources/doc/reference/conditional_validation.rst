Inline Validation
=================

The inline validation is about delegating model validation to a dedicated service.
The current validation implementation built in the Symfony2 framework is very powerful
as it allows to declare validation on a : class, field and getter. However these declarations
can take a while to code for complex rules. As rules must be a set of a ``Constraint``
and a ``Validator`` instances.

The inline validation tries to provide a nice solution by introducing an ``ErrorElement``
object. The object can be used to check assertions against the model :

.. code-block:: php

    <?php
    $errorElement
        ->with('settings.url')
            ->assertNotNull(array())
            ->assertNotBlank()
        ->end()
        ->with('settings.title')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->assertMinLength(array('limit' => 50))
            ->addViolation('ho yeah!')
        ->end();

    if (/* complex rules */) {
        $errorElement->with('value')->addViolation('Fail to check the complex rules')->end()
    }

    /* conditional validation */
    if ($this->getSubject()->getState() == Post::STATUS_ONLINE) {
        $errorElement
            ->with('enabled')
                ->assertNotNull()
                ->assertTrue()
            ->end();
    }

.. note::

    This solution relies on the validator component so validation defined through
    the validator component will be used.

.. tip::

    You can also use ``$errorElement->addConstraint(new \Symfony\Component\Validator\Constraints\NotBlank())``
    instead of calling assertNotBlank().

Using this validator
--------------------

Add the ``InlineConstraint`` class constraint to your bundle's validation configuration, for example:


.. configuration-block::

    .. code-block:: xml

        <!-- src/Application/Sonata/PageBundle/Resources/config/validation.xml -->
        <?xml version="1.0" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Application\Sonata\PageBundle\Entity\Block">
                <constraint name="Sonata\AdminBundle\Validator\Constraints\InlineConstraint">
                    <option name="service">sonata.page.cms.page</option>
                    <option name="method">validateBlock</option>
                </constraint>
            </class>
        </constraint-mapping>

    .. code-block:: yaml

        # src/Application/Sonata/PageBundle/Resources/config/validation.yml
        Application\Sonata\PageBundle\Entity\Block:
            constraints:
                - Sonata\AdminBundle\Validator\Constraints\InlineConstraint:
                    service: sonata.page.cms.page
                    method: validateBlock

There are two important options:

  - ``service``: the service where the validation method is defined
  - ``method``: the service's method to call

The method must accept two arguments:

 - ``ErrorElement``: the instance where assertion can be checked
 - ``value``: the object instance


Example from the ``SonataPageBundle``
-------------------------------------

.. code-block:: php

    <?php
    namespace Sonata\PageBundle\Block;

    use Sonata\PageBundle\Model\PageInterface;
    use Sonata\AdminBundle\Validator\ErrorElement;

    class RssBlockService extends BaseBlockService
    {
        // ... code removed for simplification

        public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
        {
            $errorElement
                ->with('settings.url')
                    ->assertNotNull(array())
                    ->assertNotBlank()
                ->end()
                ->with('settings.title')
                    ->assertNotNull(array())
                    ->assertNotBlank()
                    ->assertMinLength(array('limit' => 50))
                    ->addViolation('ho yeah!')
                ->end();
        }
    }

Using the Admin class
---------------------

This feature is deprecated and will be removed on the 2.2 branch.

The above examples show how to delegate validation to a service. For completeness, it's worth remembering that
the ``Admin`` class itself contains an empty ``validate`` method. This is automatically called, so you can override it in your own admin class:

.. code-block:: php

    // add this to your existing use statements
    use Sonata\AdminBundle\Validator\ErrorElement;

    class MyAdmin extends Admin
    {
        // add this method
        public function validate(ErrorElement $errorElement, $object)
        {
            $errorElement
                ->with('name')
                    ->assertMaxLength(array('limit' => 32))
                ->end()
            ;
        }

Troubleshooting
---------------

Make sure your validator method is being called. If in doubt, try throwing an exception:

.. code-block:: php

    public function validate(ErrorElement $errorElement, $object)
    {
        throw new \Exception(__METHOD__);
    }

There should not be any validation_groups defined for the form. If you have code like the example below in
your ``Admin`` class, remove the 'validation_groups' entry, the whole $formOptions property or set validation_groups
to an empty array:

.. code-block:: php

    protected $formOptions = array(
        'validation_groups' => array()
    );
