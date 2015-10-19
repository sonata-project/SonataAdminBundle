Sonata respects the Symfony's conventions about contributing to the code. So before going further please review the [contributing documentation of Symfony](http://symfony.com/doc/current/contributing/code/patches.html#make-a-pull-request).

## Reporting bugs

If you happen to find a bug, we kindly request you to report it. However, before submitting it, please:

  * Check the [project documentation available online](https://sonata-project.org/bundles/)

Then, if it appears that it's a real bug, you may report it using Github by following these 3 points:

  * Check if the bug is not already reported!
  * A clear title to resume the issue
  * A description of the workflow needed to reproduce the bug,

> _NOTE:_ Don't hesitate giving as much information as you can (OS, PHP version extensions...)

## Pull requests

### Writing a Pull Request

First of all, you must decide on what branch your changes will be based. If you
are sure the changes your are going to make are fully backward-compatible, you
should base your changes on the latest stable branch (`2.3` at the moment).
Otherwise, you should base your changes on the `master` branch. If you
desperately need to see some commits get merged on the older branches (`2.0`,
`2.1`, `2.2`), you still can make PR based on them, just be aware these branches
are no longer supported and your changes will probably not get merged in more
recent branches.

### Matching coding standards

Before each commit, be sure to match sonata coding standards by running the following command for fix:

```bash
make cs
```

And then, add fixed file to your commit before push.

Be sure to add only **your modified files**. If another files are fixed by cs tools, just revert it before commit.

### Sending a Pull Request

When you send a PR, just make sure that:

* You add valid test cases.
* Tests are green.
* The related documentation is up-to-date.
* You make the PR on the same branch you based your changes on. If you see commits
that you did not make in your PR, you're doing it wrong.
* Also don't forget to add a comment when you update a PR with a ping to the maintainer (``@username``), so he/she will get a notification.
* Squash your commits into one commit. (see the next chapter)

## Squash your commits

If you have 3 commits. So start with:

```bash
git rebase -i HEAD~3
```

An editor will be opened with your 3 commits, all prefixed by `pick`.

Replace all `pick` prefixes by `fixup` (or `f`) **except the first commit** of the list.

Save and quit the editor.

After that, all your commits where squashed into the first one and the commit message of the first commit.

If you would like to rename your commit message type:

```bash
git commit --amend
```

Now force push to update your PR:

```bash
git push --force
```

## Contributing to the documentation

You need to install the python tool to check and validate the sphinx syntax:

    pip install -r Resources/doc/requirements.txt

and you can check the documentation with the command:

    cd Resources/doc/
    rm -rf _build && sphinx-build -W -b html -d _build/doctrees   . _build/html

The HTML will be available in the ``_build/html`` folder.
