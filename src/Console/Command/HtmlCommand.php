<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HTML generation command.
 */
#[AsCommand(name: 'html', description: 'Generates HTML5 output')]
class HtmlCommand extends GenerateCommandBase {

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
        $this->twigRender($output, 'contributions.html.twig', $this->prepareVariables());
        return Command::SUCCESS;
    }

    /**
     * Prepared parsed YAML array as variables for twig.
     *
     * @return array
     *   Variables to pass to twig render.
     */
    protected function prepareVariables() {
        $variables = parent::prepareVariables();
        $variables['jsonld'] = $this->getJsonld();
        return $variables;
    }

    /**
     * Builds the JSON-LD structure.
     *
     * @return array
     *   The contributions as JSON-LD structure, not yet serialized.
     */
    protected function getJsonld() {
        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->get('organization.name'),
            'url' => $this->get('organization.url'),
        ];
        $data = [
            '@context' => 'https://schema.org',
            '@type' => ['ItemList', 'CreativeWork'],
            'name' => sprintf('Opens Source Contributions by %s', $this->get('organization.name')),
            'funder' => $organization,
            'itemListElement' => [],
        ];
        foreach ($this->contributions['contributions'] as $position => $contribution) {
            $project = [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareSourceCode',
                'name' => $this->get("projects.{$contribution['project']}.name"),
                'url' => $this->get("projects.{$contribution['project']}.url"),
            ];
            $item = [
                '@context' => 'https://schema.org',
                '@type' => 'CreativeWork',
                'name' => $contribution ['title'],
                'genre' => $this->getContributionTypes()[$contribution['type']],
                'contributor' => $this->get("people.{$contribution['who']}"),
                'datePublished' => $contribution['start']->format('Y-m-d'),
                'description' => $contribution['description'],
                'url' => $this->get('links.0', $contribution),
                'isBasedOn' => $project,
                'position' => $position,
            ];
            $data['itemListElement'][] = $item;
        }
        $data['numberOfItems'] = count($data['itemListElement']);
        return $data;
    }

}
