Batch actions
=============

Batch actions are actions triggered on a set of selected models (all of them or only a specific subset).

Defining new actions
--------------------

You can easily add some custom batch action in the list view. By default the ``delete`` action allows you to remove several entries at once.

Override the ``getBatchActions`` from your ``Admin`` class to define custom batch actions. For example, we define here a new ``merge`` action.

.. code-block:: php

    <?php

    // In your Admin class

    public function getBatchActions()
    {
        // retrieve the default (currently only the delete action) actions
        $actions = parent::getBatchActions();

        // check user permissions
        if($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')){
            $actions['merge']=[
                'label'            => $this->trans('action_merge', array(), 'SonataAdminBundle'),
                'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
            ];

        }

        return $actions;
    }


(Optional) Overriding the batch selection template
--------------------------------------------------

Obviously, a merge action requires two kind of selection : a set of source model to merge into one target model. By default, this bundle only enable the user to select some model, but there is only one selection kind. Thus you will need to override the ``list__batch.html.twig`` template to display both a checkbox (source selection) and a radio (target selection) for each model row. See Symfony bundle overriding mechanism for further explanation.

.. code-block:: html+jinja

    {# in Acme/ProjectBundle/Resources/views/CRUD/list__batch.html.twig #}


    {# See SonataAdminBundle:CRUD:list__batch.html.twig for the current default template #}
    <td class="sonata-ba-list-field sonata-ba-list-field-batch">
        <input type="checkbox" name="idx[]" value="{{ admin.id(object) }}" />

        {# the new radio #}
        <input type="radio" name="targetId" value="{{ admin.id(object) }}" />
    </td>


And add this:

.. code-block:: php

    <?php

    // In Acme/ProjectBundle/AcmeProjectBundle.php

    public function getParent()
    {
        return 'SonataAdminBundle';
    }


(Optional) Overriding the default relevancy check function
----------------------------------------------------------

By default, batch actions are not executed if no model was selected, and the user is notified of this lack of selection. If your custom batch action need some more complex logic to determine if an action can be performed or not, just define the ``batchActionMyActionIsRelevant`` method in your ``CRUDController`` class. This check is performed before any confirmation, to make sure there is actually something to confirm. This method may return three different values :

 - ``true``: The batch action is relevant and can be applied.
 - a string: The batch action is not relevant given the current request parameters (for example the ``target`` is missing for a ``merge`` action). The returned string is the message that inform the user why the action is aborted.
 - ``false``: Same as above, with the default "action aborted, no model selected" notification message.

.. code-block:: php

    <?php

    // In Acme/Controller/CRUDController.php

    public function batchActionMergeIsRelevant(array $normalizedSelectedIds, $allEntitiesSelected)
    {
        // here you have access to all POST parameters, if you use some custom ones
        // POST parameters are kept even after the confirmation page.
        $parameterBag = $this->get('request')->request;

        // check that a target has been chosen
        if (!$parameterBag->has('targetId')) {
            return 'flash_batch_merge_no_target';
        }

        $normalizedTargetId = $parameterBag->get('targetId');

        // if all entities are selected, a merge can be done
        if ($allEntitiesSelected) {
            return true;
        }

        // filter out the target from the selected models
        $normalizedSelectedIds = array_filter($normalizedSelectedIds,
            function($normalizedSelectedId) use($normalizedTargetId){
                return $normalizedSelectedId !== $normalizedTargetId;
            }
        );

        // if at least one but not the target model is selected, a merge can be done.
        return count($normalizedSelectedIds) > 0;
    }


Define the core action logic
----------------------------

The method ``batchActionMyAction`` will be executed to achieve the core logic. The selected models are passed to the method through a query argument retrieving them. If for some reason it makes sense to perform your batch action without the default selection method (for example you defined another way, at template level, to select model at a lower granularity), the passed query is ``null``.

.. code-block:: php

    <?php

    // In Acme/Controller/CRUDController.php

    public function batchActionMerge(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false)
        {
            throw new AccessDeniedException();
        }

        $request = $this->get('request');
        $modelManager = $this->admin->getModelManager();

        $target = $modelManager->find($this->admin->getClass(), $request->get('targetId'));

        if( $target === null){
            $this->get('session')->setFlash('sonata_flash_info', 'flash_batch_merge_no_target');

            return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
        }

        $selectedModels = $selectedModelQuery->execute();

        // do the merge work here

        try {
            foreach ($selectedModels as $selectedModel) {
                $modelManager->delete($selectedModel);
            }

            $modelManager->update($selectedModel);
        } catch (\Exception $e) {
            $this->get('session')->setFlash('sonata_flash_error', 'flash_batch_merge_error');

            return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
        }

        $this->get('session')->setFlash('sonata_flash_success', 'flash_batch_merge_success');

        return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
    }
