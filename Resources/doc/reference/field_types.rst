Field Types
===========

List and Show Actions
---------------------

There are many field types that can be used in the list action or show action :

* **array**: display value from an array
* **boolean**: display a green or red picture dependant on the boolean value
* **date**: display a formatted date. Accepts an optional ``format`` parameter
* **datetime**: display a formatted date and time. Accepts an optional ``format`` parameter
* **text**: display a text
* **textarea**: display a textarea
* **trans**: translate the value with a provided ``catalogue`` option
* **string**: display a text
* **number**: display a number
* **currency**: display a number with a provided ``currency`` option
* **percent**: display a percentage
* **choice**: uses the given value as index for the ``choices`` array and displays (and optionally translates) the matching value
* **url**: display a link
* **html**: display (and optionally truncate or strip tags from) raw html

Theses types accept an ``editable`` parameter to edit the value from within the list action.
This is currently limited to scalar types (text, integer, url...).

.. note::

    If the ``SonataIntlBundle`` is installed in the project some template types
    will be changed to use localized information.

    Option for currency type must be an official ISO code, example : EUR for "euros".
    List of ISO codes : `http://en.wikipedia.org/wiki/List_of_circulating_currencies <http://en.wikipedia.org/wiki/List_of_circulating_currencies>`_

    In ``date`` and ``datetime`` field types, ``format`` pattern must match twig's
    ``date`` filter specification, available at: `http://twig.sensiolabs.org/doc/filters/date.html <http://twig.sensiolabs.org/doc/filters/date.html>`_
    
More types might be provided based on the persistency layer defined. Please refer to their
related documentations.

Choice
^^^^^^

.. code-block:: php

    // For value `prog` is displayed text `In progress`. The `AcmeDemoBundle` catalogue will be used to translate `In progress` message.
    $listMapper->add(
        'status',
        'choice',
        array('choices' => array(
            'prep' => 'Prepared',
            'prog' => 'In progress',
            'done' => 'Done'
        ),
        'catalogue' => 'AcmeDemoBundle'
    ));

``choice`` field type also supports multiple values that can be separated by ``delimiter`` (default delimiter is a comma ",").

.. code-block:: php

    // For value `array('r', 'b')` is displayed `text `red | blue`.
    $listMapper->add(
        'colors',
        'choice',
        array(
            'multiple' => true,
            'delimiter' => ' | ',
            'choices' => array('r'=>'red', 'g'=>'green', 'b'=>'blue'))
    );

Url
^^^

Display url link to external website or controller's action.


Parameters:

* **hide_protocol**: remove protocol part from the link text
* **url**: url address (e.g. ``http://example.com``)
* **route.name**: route name (e.g. ``acme_demo_homepage``)
* **route.parameters**: array of route parameters (e.g. ``array('type'=>'example', 'display'=>'full')``)
* **route.absolute**: boolean value, create absolute or relative url address based on ``route.name`` and  ``route.parameters`` (default ``false``)
* **route.identifier_parameter_name**: parameter added to ``route.parameters``, its value is an object identifier (e.g. 'id') to create dynamic links based on rendered objects.

.. code-block:: php

    // Output for value `http://example.com`:
    // `<a href="http://example.com">http://example.com</a>`
    $listMapper->add('targetUrl', 'url');

    // Output for value `http://example.com`:
    // `<a href="http://example.com">example.com</a>`
    $listMapper->add('targetUrl', 'url', array('hide_protocol' => true));

    // Output for value `Homepage of example.com` :
    // `<a href="http://example.com">Homepage of example.com</a>`
    $listMapper->add('title', 'url', array('url' => 'http://example.com'));

    // Output for value `Acme Blog Homepage`:
    // `<a href="http://blog.example.com">Acme Blog Homepage</a>`
    $listMapper->add('title', 'url', array('route' => array(
        'name' => 'acme_blog_homepage',
        'absolute' => true
    )));

    // Output for value `Sonata is great!` (related object has identifier `123`):
    // `<a href="http://blog.example.com/xml/123">Sonata is great!</a>`
    $listMapper->add('title', 'url', array('route' => array(
        'name' => 'acme_blog_article',
        'absolute' => true,
        'parameters' => array('format' => 'xml'),
        'identifier_parameter_name' => 'id')));

.. note::

    Do not use ``url`` type with ``addIdentifier`` method, because it will create invalid nested urls.

Html
^^^^

Display (and optionally truncate or strip tags from) raw html.


Parameters:

* **strip**: Strip HTML and PHP tags from a string
* **truncate**: Truncate a string to ``length`` characters beginning from start. Implies strip. Beware of html entities. Make sure to configure your html editor to disable entities if you want to use truncate. For instance, use `config.entities <http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-entities>`_ for ckeditor
* **truncate.length**: The length to truncate the string to (default ``30``)
* **truncate.preserve**: Preserve whole words (default ``false``)
* **truncate.separator**: Separator to be appended to the trimmed string (default ``...``)

.. code-block:: php

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `<p><strong>Creating a Template for the Field</strong> and form</p>` (no escaping is done)
    $listMapper->add('content', 'html');

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `Creating a Template for the Fi...`
    $listMapper->add('content', 'html', array('strip' => true));

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `Créer un Template pour...`
    $listMapper->add('content', 'html', array('truncate' => true));

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `Creating a...`
    $listMapper->add('content', 'html', array('truncate' => array('length' => 10)));

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `Creating a Template for the Field...`
    $listMapper->add('content', 'html', array('truncate' => array('preserve' => true)));

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `Creating a Template for the Fi, etc.`
    $listMapper->add('content', 'html', array('truncate' => array('separator' => ', etc.')));

    // Output for value `<p><strong>Creating a Template for the Field</strong> and form</p>`:
    // `Creating a Template for***`
    $listMapper->add('content', 'html', array('truncate' => array(
            'length' => 20,
            'preserve' => true,
            'separator' => '***'
        )));
