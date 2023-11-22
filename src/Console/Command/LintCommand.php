<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Command\LintCommand as LintBaseCommand;

/**
 * Lint command.
 */
#[AsCommand(name: 'lint', description: 'Lint a YAML file and outputs encountered errors')]
class LintCommand extends LintBaseCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('filename', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'A file, a directory or "-" for reading from STDIN', ['contributions.yml'])
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format ("txt, json, github")')
            ->addOption('exclude', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Path(s) to exclude')
            ->addOption('parse-tags', null, InputOption::VALUE_NEGATABLE, 'Parse custom tags', null)
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lints a YAML file and outputs to STDOUT
the first encountered syntax error.

You can validates YAML contents passed from STDIN:

  <info>cat filename | php %command.full_name% -</info>

You can also validate the syntax of a file:

  <info>php %command.full_name% filename</info>

Or of a whole directory:

  <info>php %command.full_name% dirname</info>
  <info>php %command.full_name% dirname --format=json</info>

You can also exclude one or more specific files:

  <info>php %command.full_name% dirname --exclude="dirname/foo.yaml" --exclude="dirname/bar.yaml"</info>

EOF
            )
        ;
    }

}
