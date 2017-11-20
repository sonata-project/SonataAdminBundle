Sortable Sonata Type Model in Admin
===================================

This is a full working example on how to implement a sortable feature in your Sonata admin form between two entities.

Background
----------

The sortable function is already available inside Sonata for the ``ChoiceType``. But the ``ModelType`` (or sonata_type_model) extends from choice, so this function is already available in our form type.
We just need some configuration to make it work.

The goal here is to fully configure a working example to handle the following need :
User got some expectations, but some are more relevant than the others.

Pre-requisites
--------------

Configuration
^^^^^^^^^^^^^
- you already have SonataAdmin and DoctrineORM up and running.
- you already have a ``UserBundle``.
- you already have ``User`` and ``Expectation`` Entities classes.
- you already have an ``UserAdmin`` and ``ExpectationAdmin`` set up.


The recipe
----------

Part 1 : Update the data model configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The first thing to do is to update the Doctrine ORM configuration and create the join entity between ``User`` and ``Expectation``.
We are going to call this join entity ``UserHasExpectations``.

.. note::

   We can't use a ``Many-To-Many`` relation here because the joined entity will required an extra field to handle ordering.

So we start by updating ``UserBundle/Resources/config/doctrine/User.orm.xml`` and adding a ``One-To-Many`` relation.

.. code-block:: xml

	<one-to-many field="userHasExpectations" target-entity="UserBundle\Entity\UserHasExpectations" mapped-by="user" orphan-removal="true">
        <cascade>
            <cascade-persist />
        </cascade>
        <order-by>
            <order-by-field name="position" direction="ASC" />
        </order-by>
    </one-to-many>

Then update ``UserBundle/Resources/config/doctrine/Expectation.orm.xml`` and also adding a ``One-To-Many`` relation.

.. code-block:: xml

    <one-to-many field="userHasExpectations" target-entity="UserBundle\Entity\UserHasExpectations" mapped-by="expectation" orphan-removal="false">
        <cascade>
            <cascade-persist />
        </cascade>
    </one-to-many>

We now need to create the join entity configuration, create the following file in ``UserBundle/Resources/config/doctrine/UserHasExpectations.orm.xml``.

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                      xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

        <entity name="UserBundle\Entity\UserHasExpectations" table="user__expectations">
            <id name="id" type="integer">
                <generator strategy="AUTO" />
            </id>
            <field name="position" column="position" type="integer">
                <options>
                    <option name="default">0</option>
                </options>
            </field>

            <many-to-one field="user" target-entity="UserBundle\Entity\User" inversed-by="userHasExpectations" orphan-removal="false">
                <join-column name="user_id" referenced-column-name="id" on-delete="CASCADE"/>
            </many-to-one>

            <many-to-one field="expectation" target-entity="UserBundle\Entity\Expectation" inversed-by="userHasExpectations" orphan-removal="false">
                <join-column name="expectation_id" referenced-column-name="id" on-delete="CASCADE"/>
            </many-to-one>
        </entity>
    </doctrine-mapping>

Part 2 : Update the data model entities
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Update the ``UserBundle\Entity\User.php`` entity with the following :

.. code-block:: php

    // ...

    /**
     * @var UserHasExpectations[]
     */
    protected $userHasExpectations;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->userHasExpectations = new ArrayCollection;
    }

    /**
     * @param ArrayCollection $userHasExpectations
     */
    public function setUserHasExpectations(ArrayCollection $userHasExpectations)
    {
        $this->userHasExpectations = new ArrayCollection;

        foreach ($userHasExpectations as $one) {
            $this->addUserHasExpectations($one);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getUserHasExpectations()
    {
        return $this->userHasExpectations;
    }

    /**
     * @param UserHasExpectations $userHasExpectations
     */
    public function addUserHasExpectations(UserHasExpectations $userHasExpectations)
    {
        $userHasExpectations->setUser($this);

        $this->userHasExpectations[] = $userHasExpectations;
    }

    /**
     * @param UserHasExpectations $userHasExpectations
     *
     * @return $this
     */
    public function removeUserHasExpectations(UserHasExpectations $userHasExpectations)
    {
        $this->userHasExpectations->removeElement($userHasExpectations);

        return $this;
    }

    // ...

Update the ``UserBundle\Entity\Expectation.php`` entity with the following :

.. code-block:: php

    // ...

    /**
     * @var UserHasExpectations[]
     */
    protected $userHasExpectations;

    /**
     * @param UserHasExpectations[] $userHasExpectations
     */
    public function setUserHasExpectations($userHasExpectations)
    {
        $this->userHasExpectations = $userHasExpectations;
    }

    /**
     * @return UserHasExpectations[]
     */
    public function getUserHasExpectations()
    {
        return $this->userHasExpectations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    // ...

Create the ``UserBundle\Entity\UserHasExpectations.php`` entity with the following :

.. code-block:: php

    <?php
    namespace UserBundle\Entity;

    class UserHasExpectations
    {
        /**
         * @var integer $id
         */
        protected $id;

        /**
         * @var User $user
         */
        protected $user;

        /**
         * @var Expectation $expectation
         */
        protected $expectation;

        /**
         * @var integer $position
         */
        protected $position;

        /**
         * Get id
         *
         * @return integer $id
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @return User
         */
        public function getUser()
        {
            return $this->user;
        }

        /**
         * @param User $user
         *
         * @return $this
         */
        public function setUser(User $user)
        {
            $this->user = $user;

            return $this;
        }

        /**
         * @return Expectation
         */
        public function getExpectation()
        {
            return $this->expectation;
        }

        /**
         * @param Expectation $expectation
         *
         * @return $this
         */
        public function setExpectation(Expectation $expectation)
        {
            $this->expectation = $expectation;

            return $this;
        }

        /**
         * @return int
         */
        public function getPosition()
        {
            return $this->position;
        }

        /**
         * @param int $position
         *
         * @return $this
         */
        public function setPosition($position)
        {
            $this->position = $position;

            return $this;
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return (string) $this->getExpectation();
        }
    }

Part 3 : Update admin classes
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This is a very important part, the admin class **should** be created for the join entity. If you don't do that, the field will never display properly.
So we are going to start by creating this ``UserBundle\Admin\UserHasExpectationsAdmin.php`` ...

.. code-block:: php

    <?php
    namespace UserBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Form\FormMapper;

    class UserHasExpectationsAdmin extends AbstractAdmin
    {
        /**
         * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
         */
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('expectation', 'sonata_type_model', array('required' => false))
                ->add('position', 'hidden')
            ;
        }

        /**
         * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
         */
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->add('expectation')
                ->add('user')
                ->add('position')
            ;
        }
    }

... and define the service in ``UserBundle\Resources\config\admin.xml``.

.. code-block:: xml

    <service id="user.admin.user_has_expectations" class="UserBundle\Admin\UserHasExpectationsAdmin">
        <tag name="sonata.admin" manager_type="orm" group="UserHasExpectations" label="UserHasExpectations" />
        <argument />
        <argument>UserBundle\Entity\UserHasExpectations</argument>
        <argument />
    </service>

Now update the ``UserBundle\Admin\UserAdmin.php`` by adding the ``sonata_type_model`` field.

.. code-block:: php

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // ...

        $formMapper
            ->add('userHasExpectations', 'sonata_type_model', array(
                'label'        => 'User\'s expectations',
                'query'        => $this->modelManager->createQuery('UserBundle\Entity\Expectation'),
                'required'     => false,
                'multiple'     => true,
                'by_reference' => false,
                'sortable'     => true,
            ))
        ;

        $formMapper->get('userHasExpectations')->addModelTransformer(new ExpectationDataTransformer($this->getSubject(), $this->modelManager));
    }

There is two important things that we need to show here :
* We use the field ``userHasExpectations`` of the user, but we need a list of ``Expectation`` to be displayed, that's explain the use of ``query``.
* We want to persist ``UserHasExpectations``Entities, but we manage ``Expectation``, so we need to use a custom `ModelTransformer <http://symfony.com/doc/current/cookbook/form/data_transformers.html>`_ to deal with it.

Part 4 : Data Transformer
^^^^^^^^^^^^^^^^^^^^^^^^^

The last (but not least) step is create the ``UserBundle\Form\DataTransformer\ExpectationDataTransformer.php`` to handle the conversion of ``Expectation`` to ``UserHasExpectations``.

.. code-block:: php

    <?php
    namespace UserBundle\Form\DataTransformer;

    class ExpectationDataTransformer implements Symfony\Component\Form\DataTransformerInterface
    {
        /**
         * @var User $user
         */
        private $user;

        /**
         * @var ModelManager $modelManager
         */
        private $modelManager;

        /**
         * @param User         $user
         * @param ModelManager $modelManager
         */
        public function __construct(User $user, ModelManager $modelManager)
        {
            $this->user         = $user;
            $this->modelManager = $modelManager;
        }

        /**
         * {@inheritdoc}
         */
        public function transform($data)
        {
            if (!is_null($data)) {
                $results = [];

                /** @var UserHasExpectations $userHasExpectations */
                foreach ($data as $userHasExpectations) {
                    $results[] = $userHasExpectations->getExpectation();
                }

                return $results;
            }

            return $data;
        }

        /**
         * {@inheritdoc}
         */
        public function reverseTransform($expectations)
        {
            $results  = new ArrayCollection;
            $position = 0;

            /** @var Expectation $expectation */
            foreach ($expectations as $expectation) {
                $userHasExpectations = $this->create();
                $userHasExpectations->setExpectation($expectation);
                $userHasExpectations->setPosition($position++);

                $results->add($userHasExpectations);
            }

            // Remove Old values
            $qb   = $this->modelManager->getEntityManager()->createQueryBuilder();
            $expr = $this->modelManager->getEntityManager()->getExpressionBuilder();

            $userHasExpectationsToRemove = $qb->select('entity')
                                               ->from($this->getClass(), 'entity')
                                               ->where($expr->eq('entity.user', $this->user->getId()))
                                               ->getQuery()
                                               ->getResult();

            foreach ($userHasExpectationsToRemove as $userHasExpectations) {
                $this->modelManager->delete($userHasExpectations, false);
            }
            $this->modelManager->getEntityManager()->flush();

            return $results;
        }
    }

Hope this will work for you :)
