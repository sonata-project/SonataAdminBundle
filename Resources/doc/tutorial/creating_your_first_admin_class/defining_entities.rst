Defining Entities
=================

This tutorial uses the more verbose xml format of defining entities, but any
metadata driver will work fine. The ``AdminBundle`` simply interacts with the
entities as provided by Doctrine.

Model definition
----------------

Comment
~~~~~~~

.. code-block:: php

    <?php

    class Comment
    {
        const STATUS_MODERATE   = 2;
        const STATUS_VALID   = 1;
        const STATUS_INVALID = 0;

        protected $name;
        protected $email;
        protected $url;
        protected $message;
        protected $createdAt;
        protected $updatedAt;
        protected $status = self::STATUS_VALID;
        protected $post;

        public static function getStatusList()
        {
            return array(
                self::STATUS_MODERATE => 'moderate',
                self::STATUS_INVALID => 'invalid',
                self::STATUS_VALID   => 'valid',
            );
        }

        public function preInsert($object)
        {
            $object->setCreatedAt(new \DateTime);
            $object->setUpdatedAt(new \DateTime);
        }

        public function preUpdate($object)
        {
            $object->setUpdatedAt(new \DateTime);
        }
    }

Post
~~~~

.. code-block:: php

    <?php
    class Post
    {
        protected $title;
        protected $slug;
        protected $abstract;
        protected $content;
        protected $tags;
        protected $comments;
        protected $enabled;
        protected $publicationDateStart;
        protected $createdAt;
        protected $updatedAt;
        protected $commentsEnabled = true;
        protected $commentsCloseAt;
        protected $commentsDefaultStatus;

        public function __construct()
        {
            $this->tags     = new \Doctrine\Common\Collections\ArrayCollection;
            $this->comments = new \Doctrine\Common\Collections\ArrayCollection;
        }

        public function preInsert($object)
        {
            $object->setCreatedAt(new \DateTime);
            $object->setUpdatedAt(new \DateTime);
        }

        public function preUpdate($object)
        {
            $object->setUpdatedAt(new \DateTime);
        }
    }

Tag
~~~

.. code-block:: php

    class Tag
    {
        protected $name;
        protected $slug;
        protected $createdAt;
        protected $updatedAt;
        protected $enabled;
        protected $posts;

        public function preInsert($object)
        {
            $object->setCreatedAt(new \DateTime);
            $object->setUpdatedAt(new \DateTime);
        }

        public function preUpdate($object)
        {
            $object->setUpdatedAt(new \DateTime);
        }
    }


Mapping definition
------------------

Comment
~~~~~~~

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xsi="http://www.w3.org/2001/XMLSchema-instance" schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
        <entity name="Sonata\NewsBundle\Entity\Comment" table="news__comment">
            <id name="id" type="integer" column="id">
                <generator strategy="AUTO"/>
            </id>

            <field name="name"              type="string"       column="name"          />
            <field name="url"               type="string"       column="url"           />
            <field name="email"             type="string"       column="email"           />
            <field name="message"           type="text"         column="message"       />
            <field name="status"            type="integer"      column="status"        default="false" />
            <field name="createdAt"         type="datetime"     column="created_at" />
            <field name="updatedAt"         type="datetime"     column="updated_at" />

            <lifecycle-callbacks>
              <lifecycle-callback type="prePersist" method="prePersist"/>
              <lifecycle-callback type="preUpdate" method="preUpdate"/>
            </lifecycle-callbacks>

            <many-to-one field="post" target-entity="Sonata\NewsBundle\Entity\Post">
               <join-column name="post_id" referenced-column-name="id" />
            </many-to-one>
        </entity>
    </doctrine-mapping>


Post
~~~~

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xsi="http://www.w3.org/2001/XMLSchema-instance" schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
        <entity name="Sonata\NewsBundle\Entity\Post" table="news__post">

            <id name="id" type="integer" column="id">
                <generator strategy="AUTO"/>
            </id>

            <field name="title"             type="string"       column="title"           />
            <field name="abstract"          type="text"         column="abstract"           />
            <field name="content"           type="text"         column="content"           />
            <field name="enabled"           type="boolean"      column="enabled"        default="false" />
            <field name="slug"              type="string"      column="slug" />
            <field name="publicationDateStart"   type="datetime"   column="publication_date_start"    nullable="true"/>
            <field name="commentsEnabled"    type="boolean"   column="comments_enabled" default="true"/>
            <field name="commentsClose_at"   type="datetime"  column="comments_close_at" nullable="true"/>
            <field name="commentsDefaultStatus"   type="integer"  column="comments_default_status" nullable="false"/>
            <field name="createdAt"          type="datetime"   column="created_at" />
            <field name="updatedAt"          type="datetime"   column="updated_at" />

            <lifecycle-callbacks>
                <lifecycle-callback type="prePersist" method="prePersist"/>
                <lifecycle-callback type="preUpdate" method="preUpdate"/>
            </lifecycle-callbacks>

            <many-to-many
                field="tags"
                target-entity="Sonata\NewsBundle\Entity\Tag"
                inversed-by="posts"
                fetch="EAGER"
                >

                <cascade>
                   <cascade-persist />
                </cascade>

                <join-table name="news__post_tag">
                    <join-columns>
                        <join-column name="post_id" referenced-column-name="id"/>
                    </join-columns>

                    <inverse-join-columns>
                        <join-column name="tag_id" referenced-column-name="id"/>
                    </inverse-join-columns>
                </join-table>
            </many-to-many>

            <one-to-many
                field="comments"
                target-entity="Sonata\NewsBundle\Entity\Comment"
                mapped-by="post">

                <cascade>
                    <cascade-persist/>
                </cascade>
                <join-columns>
                    <join-column name="id" referenced-column-name="post_id" />
                </join-columns>

                <order-by>
                    <order-by-field name="created_at" direction="DESC" />
                </order-by>

            </one-to-many>
        </entity>
    </doctrine-mapping>


Comment
~~~~~~~

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xsi="http://www.w3.org/2001/XMLSchema-instance" schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

        <entity name="Sonata\NewsBundle\Entity\Tag" table="news__tag">

            <id name="id" type="integer" column="id">
                <generator strategy="AUTO"/>
            </id>

            <field name="name"          type="string"       column="title"           />
            <field name="enabled"       type="boolean"      column="enabled"        default="false" />
            <field name="slug"          type="string"      column="slug"    />
            <field name="createdAt"     type="datetime"   column="created_at" />
            <field name="updatedAt"     type="datetime"   column="updated_at" />

            <lifecycle-callbacks>
                <lifecycle-callback type="prePersist" method="prePersist"/>
                <lifecycle-callback type="preUpdate" method="preUpdate"/>
            </lifecycle-callbacks>

            <many-to-many field="posts" target-entity="Sonata\NewsBundle\Entity\Post" mapped-by="tags" >
            </many-to-many>

        </entity>

    </doctrine-mapping>


Generate getter and setter
--------------------------

Run the doctrine command "doctrine:generate:entities" to fill in the relevant
getter/setter methods for your new entities. This is usually accomplished by
using the "console" application in your application directory.
