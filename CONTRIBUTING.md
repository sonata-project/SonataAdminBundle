# Sonata project contribution

Thanks for you interest onto Sonata projects!

## Summary

* [Issues](#issues)
* [Pull Requests](#pull-requests)
* [Label rules]()

## Issues

First, check if you are up to date: is your version still supported, and are
you using the latest patch version?

If you are not sure this is a bug, consider posting your question on [Stack
Overflow](http://stackoverflow.com), using one of the sonata tags.
If you happen to find a bug, we kindly request you to report it. However,
before submitting it, please check the [project documentation available
online](https://sonata-project.org/bundles/).

Then, if it appears that it is indeed a real bug, you may report it using
Github by following these points are taken care of:

* Check if the bug is not already reported!
* A clear title to sum up the issue
* A description of the workflow needed to reproduce the bug. Please try to make
  sentence, dumping an error message by itself is not great.
* If your issue is an error page, you must provide us with a stack trace.  With
  recent versions of Symfony, you can even get stack traces as plain text at the
end of the page. Just look for "Stack Trace (Plain Text)", and copy/paste what
you see. **Do not** make a screenshot of the stack trace, as screenshots are
not indexed by search engines and will make it difficult for other people to
find your bug report.
* Screenshots should be considered additional data, and therefore, you should
  always provide a textual description of the bug. It is strongly recommended
to provide them when reporting UI-related bugs.
* If you need to provide code, make sure you know how to get syntactic
  coloration, in particular with [fenced code
blocks](https://help.github.com/articles/creating-and-highlighting-code-blocks/).
When you feel the code is to long, use external code pastebin like
https://gist.github.com/ or http://hastebin.com/ . If this is not sufficient,
just create a repository to show the issue.

> _NOTE:_ Don't hesitate giving as much information as you can (OS, PHP
> version, extensions...)

## Pull Requests

All the sonata team will be glad to review your code changes propositions! :smile:

But please, read the following before.

### Coding style

Each project follows [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)
and [Symfony Coding Standards](http://symfony.com/doc/current/contributing/code/standards.html) for coding style,
[PSR-4](http://www.php-fig.org/psr/psr-4/) for autoloading.

Please [install PHP Coding Standard Fixer](http://cs.sensiolabs.org/#installation)
and run this command before committing your modifications:

```bash
php-cs-fixer fix --verbose
```

### Writing a Pull Request

#### The content

Ideally, a Pull Request should concern one and **only one** subject, so that it
remains clear, and independent changes can be merged quickly.

If you want to fix a typo and improve the performance of a process, you should
try as much as possible to it in a **separate** PR, so that we can quickly
merge one while discussing the other.

The goal is to have a clear commit history and make possible revert easier.

If you found an issue/typo while writing your change that is not related to
your work, please do another PR for that. In some rare cases, you might be
forced to do it on the same PR. In this kind of situation, please add a comment
on your PR explaining why you feel it is the case.

#### The Change log

On each PR, the `CHANGELOG.md` file **has to be updated**.

There are few cases where the `CHANGELOG.md` file should not be touched:

* When you fix a bug on an unreleased feature.
* When your PR concerns only the documentation (fix or improvement).

The change log note has to be put below the `## [Unreleased]` section.

Your note can be put on one of these sections:

* `Added` for new features.
* `Changed` for changes in existing functionality.
* `Deprecated` for deprecation of features that will be removed in next major release.
* `Removed` for deprecated features removed in this release.
* `Fixed` for any bug fixes.
* `Security` to invite users to upgrade in case of vulnerabilities.

More information about the followed changelog format: [keepachangelog.com](http://keepachangelog.com/)

#### The base branch

Before writing a PR, you have to check on which branch your changes should be based.

Each project follows [semver](http://semver.org/) convention for release management.

Here is a short table resuming on which you have to start:

Kind of modification | Backward Compatible (BC) | Type of release | Branch to target        | Label |
-------------------- | ------------------------ | --------------- | ----------------------- | ----- |
Bug fixes            | Yes                      | Patch           | `2.x`   | |
Bug fixes            | No (Only if no choice)   | Major           | `master` | |
Feature              | Yes                      | Minor           | `2.x`   | |
Feature              | No (Only if no choice)   | Major           | `master` | |
Deprecation          | Yes (Have to)            | Minor           | `2.x`   | |
Deprecation removal  | No (Can't be)            | Major           | `master` | |

Notes:
  * Branch `2.x` is the branch of the **latest stable** minor release and
  has to be used for Backward compatible PRs.
  * If you PR is not **Backward Compatible** but can be, it **must** be:
    * Changing a function/method signature? Prefer create a new one and deprecated the old one.
    * Code deletion? Don't. Please deprecate it instead.
    * If your BC PR is accepted, you can do a new one on the `master` branch which remove the deprecated code.
    * SYMFONY DOC REF (same logic)?

Be aware that pull requests with BC breaks could be not accepted
or reported for next major release if BC is not possible.

If you are not sure of what to do, don't hesitate to open an issue about your PR project.

#### The commit message

Sonata is a big project with many contributors, and a big part of the job is
being able to understand the code at all times, be it when submitting a PR or
looking at the history. Good commit messages are crucial to achieve this goal.

There are already a few articles (or even single purpose websites) about this,
we cannot recommend enough the following:

* http://rakeroutes.com/blog/deliberate-git
* http://stopwritingramblingcommitmessages.com
* http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html

To sum them up, the commit message has to be crystal clear and of course,
related to the PR content.

The first line of the commit message must be short, keep it under 50
characters. It must say concisely but *precisely* what you did. The other
lines, if needed, should contain a complete description of *why* you did this.

Bad commit message subject:

```
Update README.md
```

Good commit message subject :

```
Document how to install the project
```

Also, when you specify what you did avoid commit message subjects with "Fix bug
in such and such feature". Saying you are fixing something implies the previous
implementation was wrong and yours is right, which might not be even true.
Instead, state unquestionable technical facts about your changes, not opinions.
Then, in the commit description, explain why you did that and how it fixes
something.
```
call foo::bar() instead of bar::baz()

This fixes a bug that arises when doing this or that, because baz() needs a
flux capacitor object that might not be defined.
Fixes #42
```

The description is optional but strongly recommended. It could be asked by the
team if needed. PR will often lead to complicated, hard-to-read conversations
with many links to other web pages.

The commit description should be able to live without what is said in the PR,
and should ideally sum it up in a crystal clear way, so that people do not have
to open a web browser to understand what you did.
Links to PRs/Issues and external references are of course welcome, but should
not be considered enough. When you reference an issue, make sure to use one of
the keywords described in [the dedicated github
article](https://help.github.com/articles/closing-issues-via-commit-messages/).

Good commit message with description :

```
Change web UI background color to pink

This is a consensus made on #4242 in addition to #1337.

We agreed that blank color is boring and so deja vu. Pink is the new way to do.
```
(Obviously, this commit is fake. :wink:)
