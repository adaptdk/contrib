<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Command\Command;

/**
 * Report command.
 */
abstract class GenerateCommandBase extends Command {

    use TwigRenderTrait;
    use YamlTrait;

    /**
     * Parsed YAML structure.
     *
     * @var array
     */
    protected $contributions;

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
        $variables['types'] = $this->getContributionTypes();
        $variables['generation'] = time();
        return $variables;
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
