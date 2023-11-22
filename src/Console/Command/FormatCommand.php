<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Format command.
 */
#[AsCommand(name: 'format', description: 'Formats contributions YAML file, helps when manually editing it')]
class FormatCommand extends Command {

    use YamlTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addArgument(
                'contributions-yml',
                InputArgument::OPTIONAL,
                'YAML file to use as input.',
                'contributions.yml'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $yml_file = $input->getArgument('contributions-yml');
        $this->fillContributions($yml_file);
        $this->writeYaml($yml_file, $output);
        return Command::SUCCESS;
    }

}
