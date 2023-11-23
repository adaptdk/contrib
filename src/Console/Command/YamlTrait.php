<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Format command.
 */
trait YamlTrait {

    /**
     * Parsed YAML structure.
     *
     * @var array
     */
    protected $contributions;

    /**
     * Parse the YAML file and sets contributions data member.
     *
     * @param string $yml_file
     *   YAML file to interact with.
     */
    protected function fillContributions(string $yml_file) {
        if (!file_exists($yml_file)) {
            throw new \InvalidArgumentException(sprintf('Cannot find YAML file "%s".', $yml_file));
        }
        // The extra flag allows dates to use \Datetime, which is useful when
        // serializing it back to keep the ISO 8601 format.
        $this->contributions = Yaml::parseFile($yml_file, Yaml::PARSE_DATETIME);
    }

    /**
     * Writes a YAML file after some formatting.
     *
     * @param string $yml_file
     *   YAML file to interact with.
     *
     * @throws \Exception
     *   File cannot be written correctly.
     */
    protected function writeYaml(string $yml_file, OutputInterface $output) {
        $this->prepareContributions();
        $yaml = Yaml::dump($this->contributions, 4, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        if (file_put_contents($yml_file, $yaml) == FALSE) {
            throw new \Exception(sprintf('The file "%s" could not be updated correctly', $yml_file));
        }
        $output->writeln(sprintf('The file "%s" was updated correctly', $yml_file));
    }

    /**
     * Helper to adjust contributions data member before dump.
     */
    protected function prepareContributions() {
        // Sort projects by identifier.
        ksort($this->contributions['projects']);
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
     * Get possible contribution types.
     *
     * Based on CHAOSS Types of Contributions.
     * Currently based on chaoss/wg-common commit
     * 58ee6ceacba8bd8603beb41885729dda22dac288.
     * Latest version available at https://github.com/chaoss/wg-common.
     *
     * @see https://github.com/chaoss/wg-common/blob/master/focus-areas/contributions/types-of-contributions.md
     *
     * @return string[]
     *   A map where keys are machine names to identify the contribution type,
     *   and values are their labels.
     */
    protected function getContributionTypes() {
        return [
            'code' =>'Writing Code',
            'code_review' =>'Reviewing Code',
            'triage' =>'Bug Triaging',
            'qa' =>'Quality Assurance and Testing',
            'security' =>'Security-Related Activities',
            'localization' =>'Localization/L10N and Translation',
            'event' =>'Event Organization',
            'documentation' =>'Documentation Authorship',
            'community' =>'Community Building and Management',
            'teaching' =>'Teaching and Tutorial Building',
            'troubleshooting' =>'Troubleshooting and Support',
            'creative' =>'Creative Work and Design',
            'ux' =>'User Interface, User Experience, and Accessibility',
            'social' =>'Social Media Management',
            'user-support' =>'User Support and Answering Questions',
            'write' =>'Writing Articles',
            'public_relations' =>'Public Relations - Interviews with Technical Press',
            'speak' =>'Speaking at Events',
            'marketing' =>'Marketing and Campaign Advocacy',
            'website' =>'Website Development',
            'legal' =>'Legal Council',
            'financial' =>'Financial Management',
        ];
    }

}
