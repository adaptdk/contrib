<?php

namespace Contributions\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Report command.
 */
class GenerateCommand extends Command {

    /**
     * Parsed YAML structure.
     *
     * @var array
     */
    protected $contributions;

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->setName('generate:html')
            ->setDescription('Generates HTML5 output')
            ->addArgument(
                'contributions-yml',
                InputArgument::REQUIRED,
                'YAML file with the data to process.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $yml_file = $input->getArgument('contributions-yml');
        if (!file_exists($yml_file)) {
            throw new \InvalidArgumentException(sprintf('contributions-yml file "%s" does not exists', $yml_file));
        }
        $this->contributions = Yaml::parseFile($yml_file);
        return Command::SUCCESS;
    }

}
