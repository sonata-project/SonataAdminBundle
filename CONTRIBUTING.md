Sonata respects the Symfony's conventions about contributing to the code. So
before going further please review the [contributing documentation of
Symfony](http://symfony.com/doc/current/contributing/code/patches.html#make-a-pull-request).

## Reporting bugs

If you are not sure this is a bug, consider posting your question on [Stack
Overflow](http://stackoverflow.com), using one of the sonata tags.
If you happen to find a bug, we kindly request you to report it. However,
before submitting it, please check the [project documentation available
online](https://sonata-project.org/bundles/)

Then, if it appears that it's a real bug, you may report it using Github by
following these points:

* Check if the bug is not already reported!
* A clear title to sum up the issue
* A description of the workflow needed to reproduce the bug
* If your issue is an error page, you must provide us with a stack trace.  With
  recent version of Symfony, you can even get stack traces as plain text at the
end of the page. Just look for "Stack Trace (Plain Text)", and copy/paste what
you see. **Do not** make a screenshot of the stack trace, as screenshots are
not indexed by search engines and will make it difficult for other people to
find your bug report. Screenshots should be considered additional data, you
should always provide a textual description of the bug. It is strongly
recommended to provide them when reporting UI-related bugs.
* If you need to provide code, make sure you know how to get syntactic
  coloration, in particular with [fenced code
blocks](https://help.github.com/articles/creating-and-highlighting-code-blocks/).
When you feel the code is to long, use external code pastebin like
https://gist.github.com/ or http://hastebin.com/ . If this is not sufficient,
just create a repository to show the issue.

> _NOTE:_ Don't hesitate giving as much information as you can (OS, PHP
> version, extensions...)

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

Before each commit, be sure to match sonata coding standards by running the
following command for fix:

```bash
make cs
```

And then, add fixed file to your commit before push.

Be sure to add only **your modified files**. If another files are fixed by cs
tools, just revert it before commit.

### Sending a Pull Request

When you send a PR, just make sure that:

* You add valid test cases.
* Tests are green.
* The related documentation is up-to-date.
* You make the PR on the same branch you based your changes on. If you see
  commits that you did not make in your PR, you're doing it wrong.
* Also don't forget to add a comment when you update a PR with a ping to the
  maintainer (``@username``), so he/she will get a notification.
* Squash your commits into one commit. (see the next chapter)
* Some changes, especially UI changes, might affect other bundles. If you think
  this is the case, we recommend you test your changes against [the Sonata
sandbox](https://github.com/sonata-project/sandbox).

## Squash your commits

Sometimes, maintainers will ask you to squash your commits, for example if you
make a mistake in a first commit, and fix the mistake a second commit. No one
needs to know about the mistakes you made.
Please note however, that you do not have to always squash your commits, for
instance if you fix cs on a file before working it, the cs fix should stay in a
separate commit, so that the diff of the other commit stays easy to read.
If you have many commits in your PR that you do not wish to squash together,
consider making several PRs. That said, here is how to squash commits:

If you have 3 commits. So start with:

```bash
git rebase -i HEAD~3
```

An editor will be opened with your 3 commits, all prefixed by `pick`.

Replace all `pick` prefixes by `fixup` (or `f`) **except the first commit** of
the list.

Save and quit the editor.

After that, all your commits where squashed into the first one and the commit
message of the first commit.

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
