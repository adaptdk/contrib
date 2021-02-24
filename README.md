# Open Source Contributions Log

This project tries to gather information about
[open source](https://opensource.org/osd) contributions for a given
organization.

## Producing an HTML file

First, make sure you installed dependencies, e.g. `composer install`.

There is some scripting in place to generate an `HTML` page based on the data.
It also contains `JSON-LD` output that may be useful if the page is public at
some point.

You may want to try the following command.

    ./contriblog html > contributions.html

## Adding entries

- Use the helper, e.g. `./contriblog add`, or
- Manually edit the YAML file, and process it with `./contriblog format` to get
  it on the standard output.

## YAML format

The YAML file tries to be self-explanatory.
It tries to describe contributions thinking in possible future use of the data.

Types are [CHAOSS Types of
Contributions](https://github.com/chaoss/wg-common/blob/master/focus-areas/what/types-of-contributions.md).

Contributions are sorted by date.

You may want to enable the git pre-commit hook, e.g. with
`git config core.hooksPath .githooks`.

## References

- [Community Health Analytics Open Source Software, CHAOSS](https://chaoss.community/)
  provides guidance about data measurement around open source software, but it
  also provides general guidance, that this project can use.
- [CHAOSS Common Metrics Working Group (Common WG)](https://github.com/chaoss/wg-common)
  contains more direct suggestions.

## Credit

This project has been initially developed by [Adapt](https://adaptagency.com/).
