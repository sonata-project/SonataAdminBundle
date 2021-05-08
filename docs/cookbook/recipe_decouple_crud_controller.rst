Decouple from CRUDController
============================

.. versionadded:: 3.99

    The ability to inject an Admin to an action and ``AdminFetcherInterface`` service were introduced in 3.99.

When creating custom actions, we can create our controllers without extending ``CRUDController``. What we usually need
is to access the ``admin`` instance associated to the action, to do so we can use a param converter or
the ``AdminFetcherInterface`` service.

If you are using ``SensioFrameworkExtraBundle``, then you can add your Admin as parameter of the action::

    // src/Controller/CarAdminController.php

    namespace App\Controller;

    use Symfony\Component\HttpFoundation\RedirectResponse;

    final class CarAdminController
    {
        public function clone(CarAdmin $admin, Request $request)
        {
            $object = $admin->getSubject();

            // ...

            $request->getSession()->getFlashBag()->add('sonata_flash_success', 'Cloned successfully');

            return new RedirectResponse($admin->generateUrl('list'));
        }
    }

Or you can use ``AdminFetcherInterface`` service to fetch the admin from the request, in this example we transformed
the controller to make it Invokable::

    // src/Controller/CarAdminController.php

    namespace App\Controller;

    use Symfony\Component\HttpFoundation\RedirectResponse;

    final class CarAdminSoldAction
    {
        /**
         * @var AdminFetcherInterface
         */
        private $adminFetcher;

        public function __construct(AdminFetcherInterface $adminFetcher)
        {
            $this->adminFetcher = $adminFetcher;
        }

        public function __invoke(Request $request)
        {
            $admin = $this->adminFetcher->get($request);

            $object = $admin->getSubject();

            // ...

            $request->getSession()->getFlashBag()->add('sonata_flash_success', 'Sold successfully');

            return new RedirectResponse($admin->generateUrl('list'));
        }
    }

Now we only need to add the new route in ``configureRoutes``::

    use App\Controller\CarAdminCloneAction;
    use Sonata\AdminBundle\Route\RouteCollection;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('clone', $this->getRouterIdParameter().'/clone', [
                '_controller' => 'App\Controller\CarAdminController::clone',
            ])

            // Using invokable controller:
            ->add('sold', $this->getRouterIdParameter().'/sold', [
                '_controller' => CarAdminSoldAction::class,
            ]);
    }
