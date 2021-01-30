<?php

namespace Contributions\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Command\LintCommand as LintBaseCommand;

/**
 * Lint command.
 */
class LintCommand extends LintBaseCommand {

    protected static $defaultName = 'lint';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Lints a file and outputs encountered errors')
            ->addArgument('filename', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'A file, a directory or "-" for reading from STDIN', ['contributions.yml'])
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format', 'txt')
            ->addOption('parse-tags', null, InputOption::VALUE_NONE, 'Parse custom tags')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lints a YAML file and outputs to STDOUT
the first encountered syntax error.

You can validate YAML contents passed from STDIN:

  <info>cat filename | php %command.full_name% -</info>

You can also validate the syntax of a file:

  <info>php %command.full_name% filename</info>

Or of a whole directory:

  <info>php %command.full_name% dirname</info>
  <info>php %command.full_name% dirname --format=json</info>

EOF
            )
        ;
    }

}
