Sonata respects the symfony’s conventions about contributing to the code. So before going further please review the [contributing documentation of Symfony](http://symfony.com/doc/current/contributing/code/patches.html#make-a-pull-request).

## Reporting bugs

If you happen to find a bug, we kindly request you to report it. However, before submitting it, please:

  * Check the [project documentation available online](http://sonata-project.org/bundles/)

Then, if it appears that it’s a real bug, you may report it using Github by following these 3 points:

  * Check if the bug is not already reported!
  * A clear title to resume the issue
  * A description of the workflow needed to reproduce the bug,

> _NOTE:_ Don’t hesitate giving as much information as you can (OS, PHP version extensions …)

## Sending a Pull Request

When you send a PR, just make sure that:

* You add valid test cases.
* Tests are green.
* The related documentation is up-to-date.
* Also don't forget to add a comment when you update a PR with a ping to the maintainer (``@username``), so he/she will get a notification.

## Contributing to the documentation

You need to install the python tool to check and validate the sphinx syntax:

    pip install -r Resources/doc/requirements.txt

and you can check the documentation with the command:

    cd Resources/doc/
    rm -rf _build && sphinx-build -W -b html -d _build/doctrees   . _build/html

The html will be available in the ``_build/html`` folder.
