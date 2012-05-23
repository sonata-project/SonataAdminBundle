Inline Validation
=================

The inline validation is about delegating model validation to a dedicated service.
The current validation implementation built in the Symfony2 framework is very powerful
as it allows to declare validation on : class, field and getter. However these declarations
can take a while to code for complex rules. Rules must be a set of a ``Constraint``
and ``Validator`` instances.

The inline validation tries to provide a nice solution by introducting an ``ErrorElement``
object. The object can be used to check assertion against the model :

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

Using this validator
--------------------

Just add the ``InlineConstraint`` class constraint, like this:

.. code-block:: xml

    <class name="Application\Sonata\PageBundle\Entity\Block">
        <constraint name="Sonata\AdminBundle\Validator\Constraints\InlineConstraint">
            <option name="service">sonata.page.cms.page</option>
            <option name="method">validateBlock</option>
        </constraint>
    </class>

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
