# Open Source contributions

This project tries to gather information about
[open source](https://opensource.org/osd) contributions made by
[Adapt](https://adaptagency.com/).

This is not the complete list of contributions that Adapt has done, but a useful
set that can be used.
For now it contains information mainly starting at 2021.

## Producing an HTML

First, make sure you installed dependencies, e.g. `composer install`.

There is some scripting in place to generate an `HTML` page based on the data.
It also contains `JSON-LD` output that may be useful if the page is public at
some point.

You may want to try the following command.

    php helper.php generate:html contributions.yml > contributions.html

## How to add entries

Feel free to post new additions:

- Add a new PR, or
- Send some lines/links at slack #contributions channel.

I both cases please at-mention Marco.

## YAML format

The YAML file tries to be self-explanatory, when in doubt just ask on slack.
It tries to describe contributions thinking in possible future use of the data.

You may want to [install
yamllint](https://yamllint.readthedocs.io/en/stable/quickstart.html), and then
enable the git pre-commit hook, e.g. with `git config core.hooksPath .githooks`.

## References

- [Community Health Analytics Open Source Software, CHAOSS](https://chaoss.community/)
  provides guidance about data measurement around open source software, but it
  also provides general guidance, that this project can use.
- [CHAOSS Common Metrics Working Group (Common WG)](https://github.com/chaoss/wg-common)
  contains more direct suggestions.
