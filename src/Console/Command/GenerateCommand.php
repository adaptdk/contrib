<?php

namespace Contributions\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Report command.
 */
class GenerateCommand extends Command {

    use YamlTrait;

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
            ->setName('html')
            ->setDescription('Generates HTML5 output')
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
     * Renders twig template to output.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *   Output object.
     * @param string $template_name
     *   Name of the twig template to use.
     * @param array $variables
     *   Variables to pass to twig render.
     */
    protected function twigRender(OutputInterface $output, $template_name, $variables) {
        $loader = new FilesystemLoader('templates');
        $twig = new Environment($loader, [
            'cache' => 'cache',
        ]);
        $output->write($twig->render($template_name, $variables));
    }

    /**
     * Helper to get a value from contributions nested array dynamically.
     *
     * @param string $address
     *   List of keys separated by periods.
     *   E.g. 'organization.name' will extract
     *   $this->contributions['organization']['name'].
     * @param array $item
     *   The array to walk. Default to contributions data member.
     *
     * @return mixed
     *   The requested value, or an empty string if not found.
     */
    protected function get(string $address, array $item = NULL) {
        if (empty($address)) {
            return '';
        }
        if (is_null($item)) {
            $item = $this->contributions;
        }
        $parts = explode('.', $address);
        foreach ($parts as $part) {
            if (empty($item[$part])) {
                return '';
            }
            $item = $item[$part];
        }
        return $item;
    }

    /**
     * Prepared parsed YAML array as variables for twig.
     *
     * @return array
     *   Variables to pass to twig render.
     */
    protected function prepareVariables() {
        $variables = $this->contributions;
        $variables['jsonld'] = $this->getJsonld();
        $variables['generation'] = time();
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
                'genre' => $this->get("types.{$contribution['type']}"),
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

    /**
     * Filter contributions based on project tags.
     *
     * @param string[] $project_tags
     *   List of project tags to filter by.
     */
    protected function filterByTags(array $project_tags) {
        if (empty($project_tags)) {
            // Nothing to filter.
            return;
        }
        // Filter the contributions set.
        $this->contributions['contributions'] = array_filter($this->contributions['contributions'], function ($contribution) use ($project_tags) {
            $current_project_tags = $this->get("projects.{$contribution['project']}.tags");
            return !empty(array_intersect($current_project_tags, $project_tags));
        });
    }

    /**
     * Filter contributions based on contribution types.
     *
     * @param string[] $types
     *   List of contribution types to filter by.
     */
    protected function filterByTypes(array $types) {
        if (empty($types)) {
            // Nothing to filter.
            return;
        }
        // Filter the contributions set.
        $this->contributions['contributions'] = array_filter($this->contributions['contributions'], function ($contribution) use ($types) {
            if (empty($contribution['type'])) {
                // No value set, skip.
                return false;
            }
            return in_array($contribution['type'], $types);
        });
    }

}
