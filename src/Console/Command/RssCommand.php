<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RSS generation command.
 */
#[AsCommand(name: 'rss', description: 'Generates RSS output')]
class RssCommand extends FeedCommandBase {

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addArgument(
                'contributions-yml',
                InputArgument::OPTIONAL,
                'YAML file with the data to process.',
                'contributions.yml'
            )
            ->addOption(
                'tag',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'A project tag to filter the output.',
                []
            )
            ->addOption(
                'type',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'A contribution type to filter the output.',
                []
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $yml_file = $input->getArgument('contributions-yml');
        $this->fillContributions($yml_file);
        $this->filterByTags($input->getOption('tag'));
        $this->filterByTypes($input->getOption('type'));
        $this->generateFeed($output);
        return Command::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function generateFeed(OutputInterface $output): void {
        $this->twigRender($output, 'contributions.rss.twig', $this->prepareVariables());
    }
}
