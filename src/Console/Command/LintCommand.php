<?php

namespace Contributions\Console\Command;

use Symfony\Component\Yaml\Command\LintCommand as LintBaseCommand;

/**
 * Lint command.
 */
class LintCommand extends LintBaseCommand {

    protected static $defaultName = 'lint';

}
