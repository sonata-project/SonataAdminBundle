Securing route id's with HashId
===============================

If you're working on an app where you need to hide the ID's from the
routes then a good way to do it would be to use HashId_. There is
a `HashId Symfony Bundle`_ which you can install and configure.

Overriding the output of all URLs
---------------------------------

First you will need to encoding all the URLs that Sonata outputs,
below I have chosen to use the ``encodeHex`` function but there is
also an ``encode`` function. For more information on this see the
documentation in the `HashId Symfony Bundle`_

.. code:: php

    class MyAdmin extends AbstractAdmin
    {
        /**
         * @var HashId
         */
        private $hashId

        /**
         * @param string $code
         * @param string $class
         * @param string $baseControllerName
         * @param HashId $hashId
         */
        public function __construct($code, $class, $baseControllerName, HashId $hashId)
        {
            parent::__construct($code, $class, $baseControllerName);
            $this->hashId = $hashId
        }

        ... other configurations

        /**
         * @param string $name
         * @param mixed $object
         * @param array $parameters
         * @param bool|int $absolute
         * @return string
         */
        public function generateObjectUrl($name, $object, array $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
        {
            $parameters['id'] = $this->hashId->encodeHex($this->getUrlsafeIdentifier($object));

            return $this->generateUrl($name, $parameters, $absolute);
        }
    }


Now your routes will change from ``/product/1/edit`` to
``/product/af5/edit``, child routes will also be changed if you have
chosen to use them e.g. ``product/af5/child/e6D/edit``. So now you will
have to tell your controllers to decode these. For this implement a
class that uses the ``RouteIdHandlerInterface`` to decode the id held
in the request. For example

.. code:: php

    class HashIdRouteIdHandler implements RouteIdHandlerInterface
    {
        /**
         * @var HashId
         */
        private $hashId

        /**
         * @param HashId $hashId
         */
        public function __construct(HashId $hashId)
        {
            $this->hashId = $hashId
        }

        /**
         * @param Request $request
         * @param AdminInterface $admin
         *
         * @return int|string
         */
        public function getIdFromRequest(Request $request, AdminInterface $admin)
        {
            return $this->hashId->decodeHex($request->get($admin->getIdParameter()));
        }
    }

You'll need to tell Sonata admin to use your new handler which will use
the new handler for all routes of your admin suite

.. code:: php

    # config.yml

    sonata_admin:
        route_id_handler: hashid_route_id_handler


However, If you want to selectively implement this into some of your Admin
sections and not others then we would advise that you create an
interface, and make your Admin implement that and then detect it as
required. E.g

.. code:: php

    /**
     * @return int|string
     */
    public function getIdFromRequest(Request $request, AdminInterface $admin)
    {
        if ($admin instanceof HashedAdminInterface) {
            return $this->hashId->decodeHex($request->get($admin->getIdParameter()));
        }

        return $request->get($admin->getIdParameter());
    }

Be aware that this is just an example of ``HashedAdminInterface`` it is
not included with Sonata Admin and you can use any sort of test to
determine if your request will need decoding.

.. _HashId: http://hashids.org/
.. _HashId Symfony Bundle: https://github.com/roukmoute/HashidsBundle
