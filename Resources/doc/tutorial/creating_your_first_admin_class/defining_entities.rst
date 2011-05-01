Defining Entities
=================

This tutorial use the verbose xml definition, the other alternative will be to use the annotation driver.



Model definition
----------------

Comment
~~~~~~~

.. code-block:: php

    class Comment
    {
        const STATUS_MODERATE   = 2;
        const STATUS_VALID   = 1;
        const STATUS_INVALID = 0;

        protected $name;
        protected $email;
        protected $url;
        protected $message;
        protected $created_at;
        protected $updated_at;
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

    class Post
    {
        protected $title;
        protected $slug;
        protected $abstract;
        protected $content;
        protected $tags;
        protected $comments;
        protected $enabled;
        protected $publication_date_start;
        protected $created_at;
        protected $updated_at;
        protected $comments_enabled = true;
        protected $comments_close_at;
        protected $comments_default_status;

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
        protected $created_at;
        protected $updated_at;
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
            <field name="created_at"    type="datetime"   column="created_at" />
            <field name="updated_at"    type="datetime"   column="updated_at" />

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
            <field name="publication_date_start"   type="datetime"   column="publication_date_start"    nullable="true"/>
            <field name="comments_enabled"    type="boolean"   column="comments_enabled" default="true"/>
            <field name="comments_close_at"   type="datetime"  column="comments_close_at" nullable="true"/>
            <field name="comments_default_status"   type="integer"  column="comments_default_status" nullable="false"/>
            <field name="created_at"    type="datetime"   column="created_at" />
            <field name="updated_at"    type="datetime"   column="updated_at" />

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
            <field name="created_at"    type="datetime"   column="created_at" />
            <field name="updated_at"    type="datetime"   column="updated_at" />

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

start the doctrine command : php project/console doctrine:generate:entities

At this point doctrine just added all required setter and getter.