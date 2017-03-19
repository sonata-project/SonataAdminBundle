Batch actions
=============

Batch actions are actions triggered on a set of selected objects. By default,
Admins have a ``delete`` action which allows you to remove several entries at once.

Defining new actions
--------------------

To create a new custom batch action which appears in the list view follow these steps:

Override ``configureBatchActions()`` in your ``Admin`` class to define the new batch actions
by adding them to the ``$actions`` array. Each key represent a batch action and could contain these settings:

- **label**: The name to use when offering this option to users, should be passed through the translator
  (default: the label is generated via the labelTranslatorStrategy)
- **translation_domain**: The domain which will be used to translate the key.
  (default: the translation domain of the admin)
- **ask_confirmation**: defaults to true and means that the user will be asked
  for confirmation before the batch action is processed

For example, lets define a new ``merge`` action which takes a number of source items and
merges them onto a single target item. It should only be available when two conditions are met:

- the EDIT and DELETE routes exist for this Admin (have not been disabled)
- the logged in administrator has EDIT and DELETE permissions

.. code-block:: php

    <?php
    // in your Admin class

    public function configureBatchActions($actions)
    {
        if (
          $this->hasRoute('edit') && $this->hasAccess('edit') &&
          $this->hasRoute('delete') && $this->hasAccess('delete')
        ) {
            $actions['merge'] = array(
                'ask_confirmation' => true
            );

        }

        return $actions;
    }

Define the core action logic
----------------------------

The method ``batchAction<MyAction>`` will be executed to process your batch in your ``CRUDController`` class. The selected
objects are passed to this method through a query argument which can be used to retrieve them.
If for some reason it makes sense to perform your batch action without the default selection
method (for example you defined another way, at template level, to select model at a lower
granularity), the passed query is ``null``.

.. note::

    You can check how to declare your own ``CRUDController`` class in the Architecture section.

.. code-block:: php

    <?php
    // src/AppBundle/Controller/CRUDController.php

    namespace AppBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as BaseController;
    use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    class CRUDController extends BaseController
    {
        /**
         * @param ProxyQueryInterface $selectedModelQuery
         * @param Request             $request
         *
         * @return RedirectResponse
         */
        public function batchActionMerge(ProxyQueryInterface $selectedModelQuery, Request $request = null)
        {
            $this->admin->checkAccess('edit');
            $this->admin->checkAccess('delete');

            $modelManager = $this->admin->getModelManager();

            $target = $modelManager->find($this->admin->getClass(), $request->get('targetId'));

            if ($target === null){
                $this->addFlash('sonata_flash_info', 'flash_batch_merge_no_target');

                return new RedirectResponse(
                    $this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters()))
                );
            }

            $selectedModels = $selectedModelQuery->execute();

            // do the merge work here

            try {
                foreach ($selectedModels as $selectedModel) {
                    $modelManager->delete($selectedModel);
                }

                $modelManager->update($selectedModel);
            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'flash_batch_merge_error');

                return new RedirectResponse(
                    $this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters()))
                );
            }

            $this->addFlash('sonata_flash_success', 'flash_batch_merge_success');

            return new RedirectResponse(
                $this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters()))
            );
        }

        // ...
    }


(Optional) Overriding the batch selection template
--------------------------------------------------

A merge action requires two kinds of selection: a set of source objects to merge from
and a target object to merge into. By default, batch_actions only let you select one set
of objects to manipulate. We can override this behavior by changing our list template
(``list__batch.html.twig``) and adding a radio button to choose the target object.

.. code-block:: html+jinja

    {# src/AppBundle/Resources/views/CRUD/list__batch.html.twig #}
    {# see SonataAdminBundle:CRUD:list__batch.html.twig for the current default template #}

    {% extends admin.getTemplate('base_list_field') %}

    {% block field %}
        <input type="checkbox" name="idx[]" value="{{ admin.id(object) }}" />

        {# the new radio button #}
        <input type="radio" name="targetId" value="{{ admin.id(object) }}" />
    {% endblock %}


And add this:

.. code-block:: php

    <?php
    // src/AppBundle/AppBundle.php

    public function getParent()
    {
        return 'SonataAdminBundle';
    }

See the `Symfony bundle overriding mechanism`_
for further explanation of overriding bundle templates.


(Optional) Overriding the default relevancy check function
----------------------------------------------------------

By default, batch actions are not executed if no object was selected, and the user is notified of
this lack of selection. If your custom batch action needs more complex logic to determine if
an action can be performed or not, just define a ``batchAction<MyAction>IsRelevant`` method
(e.g. ``batchActionMergeIsRelevant``) in your ``CRUDController`` class. This check is performed
before the user is asked for confirmation, to make sure there is actually something to confirm.

This method may return three different values:

 - ``true``: The batch action is relevant and can be applied.
 - ``false``: Same as above, with the default "action aborted, no model selected" notification message.
 - ``string``: The batch action is not relevant given the current request parameters
   (for example the ``target`` is missing for a ``merge`` action).
   The returned string is a message displayed to the user.

.. code-block:: php

    <?php
    // src/AppBundle/Controller/CRUDController.php

    namespace AppBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as BaseController;
    use Symfony\Component\HttpFoundation\Request;

    class CRUDController extends BaseController
    {
        public function batchActionMergeIsRelevant(array $selectedIds, $allEntitiesSelected, Request $request = null)
        {
            // here you have access to all POST parameters, if you use some custom ones
            // POST parameters are kept even after the confirmation page.
            $parameterBag = $request->request;

            // check that a target has been chosen
            if (!$parameterBag->has('targetId')) {
                return 'flash_batch_merge_no_target';
            }

            $targetId = $parameterBag->get('targetId');

            // if all entities are selected, a merge can be done
            if ($allEntitiesSelected) {
                return true;
            }

            // filter out the target from the selected models
            $selectedIds = array_filter($selectedIds,
                function($selectedId) use($targetId){
                    return $selectedId !== $targetId;
                }
            );

            // if at least one but not the target model is selected, a merge can be done.
            return count($selectedIds) > 0;
        }

        // ...
    }

(Optional) Executing a pre batch hook
-------------------------------------

In your admin class you can create a ``preBatchAction`` method to execute something before doing the batch action.
The main purpose of this method is to alter the query or the list of selected ids.

.. code-block:: php

    <?php
    // in your Admin class

    public function preBatchAction($actionName, ProxyQueryInterface $query, array & $idx, $allElements)
    {
        // altering the query or the idx array
        $foo = $query->getParameter('foo')->getValue();

        // Doing something with the foo object
        // ...

        $query->setParameter('foo', $bar);
    }

.. _Symfony bundle overriding mechanism: http://symfony.com/doc/current/cookbook/bundles/inheritance.html
