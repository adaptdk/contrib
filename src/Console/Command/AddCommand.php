<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

/**
 * Add command.
 */
#[AsCommand(name: 'add', description: 'Add a new contributions entry to the YAML file')]
class AddCommand extends Command {

    use YamlTrait;

    /**
     * Label for undefined person.
     *
     * @var string
     */
    const UNDEFINED_PERSON= 'New person';

    /**
     * Key for undefined person.
     *
     * @var string
     */
    const UNDEFINED_PERSON_KEY = 0;

    /**
     * Label for undefined project.
     *
     * @var string
     */
    const UNDEFINED_PROJECT = 'New project';

    /**
     * Key for undefined project.
     *
     * @var string
     */
    const UNDEFINED_PROJECT_KEY = 'new';

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addArgument(
                'contributions-yml',
                InputArgument::OPTIONAL,
                'YAML file to use as input if exists, and that will be edited or generated.',
                'contributions.yml'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $yml_file = $input->getArgument('contributions-yml');
        try {
            $this->fillContributions($yml_file);
        }
        catch (\InvalidArgumentException $exception) {
            // File does not exist yet.
            $create_file = confirm(
                label: sprintf('The "%s" file does not exist yet. Do you want to generate it? (y/n) ', $yml_file),
                default: false,
                hint: 'A contributions file is needed to continue.'
            );
            if (!$create_file) {
                // Nothing else to do, cannot continue, hence fail.
                $output->writeln('<error>A contributions YAML file is needed to continue.</error>');
                $output->writeln('<error>See an examples directory or accept to generate it while running the add command.</error>');
                return Command::FAILURE;
            }
            $this->generateMinimalYaml();
            $this->getOrganization();
        }
        $project = $this->getProject();
        $contribution = $this->getContribution($project, $input, $output);
        $contribution = $this->getContribution($project);
        $this->contributions['contributions'][] = $contribution;
        $this->writeYaml($yml_file, $output);
        return Command::SUCCESS;
    }

    /**
     * Helper to get organization.
     *
     * @return array
     *   A map with two keys, name and url, for the organization.
     */
    protected function getOrganization() {
        $not_empty = self::getNotEmptyClosure();
        $name = text(
            label: '[1/2] What is the name of the organization?',
            placeholder: 'Acme Inc',
            required: true,
            validate: $not_empty
        );
        $url = text(
            label: '[2/2] What is main URL for the organization?',
            placeholder: 'https://example.org',
            required: true,
            validate: $not_empty
        );
        $this->contributions['organization'] = [
            'name' => $name,
            'url' => $url,
        ];
        return $this->contributions['organization'];
    }

    /**
     * Helper to get related project.
     *
     * @return string
     *   The project to use, e.g. drupal/migrate_plus.
     */
    protected function getProject() {
        $projects = [self::UNDEFINED_PROJECT_KEY => sprintf('%s (%s)', self::UNDEFINED_PROJECT, self::UNDEFINED_PROJECT_KEY)];
        ksort($this->contributions['projects']);
        foreach ($this->contributions['projects'] as $project_key => $project) {
            $projects[$project_key] = sprintf('%s (%s)', $project['name'], $project_key);
        }
        $project_search_item = suggest(
            label: '[1/7] Which project received the contribution?',
            options: fn (string $value) => match (true) {
                empty($value) => $projects,
                default => array_filter($projects, function ($v, $k) use ($value) {
                    return str_contains($v, $value);
                }, ARRAY_FILTER_USE_BOTH),
            },
            validate: fn (string $value) => match (true) {
                in_array($value, $projects) => null,
                default => sprintf('Project "%s" is invalid.', $value),
            }
        );
        $project = array_search($project_search_item, $projects);
        if ($project == self::UNDEFINED_PROJECT_KEY) {
            $not_empty = self::getNotEmptyClosure();
            $machine_name = text(
                label: 'What is the machine name of the project?',
                placeholder: 'drupal/migrate_plus',
                required: true,
                validate: $not_empty
            );
            $name = text(
                label: 'What is the name of the project?',
                placeholder: 'Migrate Plus',
                required: true,
                validate: $not_empty
            );
            $url = text(
                label: 'What is the main URL of the project?',
                placeholder: 'https://www.drupal.org/project/migrate_plus',
                required: true,
                validate: $not_empty
            );
            $tags = textarea(
                label: 'Please provide tags for the project',
                placeholder: 'drupal',
                hint: 'One tag per line',
                validate: $not_empty
            );
            $tags = self::cleanEmpty($tags);
            $this->contributions['projects'][$machine_name] = [
                'name' => $name,
                'url' => $url,
                'tags' => $tags,
            ];
            $project = $machine_name;
        }
        return $project;
    }

    /**
     * Helper to get the contribution data.
     *
     * @param string $project
     *   The project to use, e.g. drupal/migrate_plus.
     *
     * @return array
     *   The contribution data.
     */
    protected function getContribution(string $project) {
        $contribution = ['project' => $project];
        $not_empty = self::getNotEmptyClosure();
        $contribution['title'] = text(
            label: '[2/7] Title',
            hint: 'Please give the contribution a title.',
            required: true,
            validate: $not_empty
        );
        $contribution['type'] = select(
            label: '[2/7] Type',
            hint: 'What was the main type of the contribution?',
            options: $this->getContributionTypes(),
            default: 'code',
            required: true,
        );
        $contribution['who'] = $this->getPerson();
        $today = date('Y-m-d');
        $start = text(
            label: '[5/7] Date',
            hint: 'When was the contribution first published?',
            default: $today,
            required: true,
            validate: function ($value) {
                if (empty($value) || strtotime($value) === FALSE) {
                    return 'Invalid date. Please provide a time string like 2020-01-22.';
                }
            },
        );
        $contribution['start'] = new \Datetime('@' . strtotime($start), new \DateTimeZone('UTC'));
        $contribution['description'] = textarea(
           label: '[6/7] Description',
           hint: 'How would you describe the contribution?',
           validate: $not_empty,
        );
        $links = textarea(
           label: '[7/7] Links',
           hint: 'Please provide public links related to the contribution? (one per line)',
           validate: $not_empty,
        );
        $contribution['links'] = self::cleanEmpty($links);
        return $contribution;
    }

    /**
     * Helper to get related contributor.
     *
     * @return string
     *   The person identifier, e.g. jsaramago.
     */
    protected function getPerson() {
        $people = $this->contributions['people'];
        array_walk($people, fn(&$label, $key) => $label = sprintf('%s (%s)', $label, $key));
        $people = [self::UNDEFINED_PERSON_KEY => self::UNDEFINED_PERSON] + $people;
        $person = select(
            label: '[4/7] Person',
            hint: 'Who is making the the contribution?',
            options: $people,
            default: self:: UNDEFINED_PERSON_KEY,
            required: true,
        );
        if ($person == self::UNDEFINED_PERSON_KEY) {
            $not_empty = self::getNotEmptyClosure();
            $name = text(
                label: 'Name',
                placeholder: 'José Saramago',
                hint: 'What is the name of the person?',
                required: true,
                validate: $not_empty
            );
            $machine_name = text(
                label: 'Identifier',
                placeholder: 'jsaramago',
                hint: 'What is the identifier for the person?',
                required: true,
                validate: $not_empty
            );
            $this->contributions['people'][$machine_name] = $name;
            $person = $machine_name;
        }
        return $person;
    }

    /**
     * Helper to adjust contributions data member before dump.
     */
    protected function prepareContributions() {
        // Sort people by identifier.
        ksort($this->contributions['people']);
        // Sort contributions by start date.
        usort($this->contributions['contributions'], function($item1, $item2) {
            if ($item1['start'] == $item2['start']) {
                return 0;
            }
            return ($item1['start'] < $item2['start']) ? -1 : 1;
        });
    }

    /**
     * Empty validator.
     */
    public static function isNotEmpty($value) {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (empty($value)) {
            throw new \RuntimeException('Please provide a non-empty value.');
        }
        return $value;
    }

    /**
     * Normalizer helper to filter out empty lines.
     */
    public static function cleanEmpty($value) {
        $lines = explode(PHP_EOL, $value);
        array_walk($lines, fn(&$line) => $line = trim($line));
        $lines = array_filter($lines);
        return array_values($lines);
    }

    protected function generateMinimalYaml() {
        $this->contributions = [
            'organization' => [],
            'people' => [],
            'projects' => [],
            'contributions' => [],
            'config' => [],
        ];
    }

    /**
     * Helper to get not empty validation closure.
     */
    private static function getNotEmptyClosure(): \Closure {
        return fn(string $value) => match (true) {
            self::isNotEmpty($value) => 'Empty value',
            default => null,
        };
    }
}
