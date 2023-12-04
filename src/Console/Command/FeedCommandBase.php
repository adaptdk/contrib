<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * A base command for feed generation.
 */
abstract class FeedCommandBase extends GenerateCommandBase {

    /**
     * Prints a feed to the output.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *   Output object.
     */
    abstract protected function generateFeed(OutputInterface $output): void;

    /**
     * {@inheritdoc}
     */
    protected function prepareVariables() {
        $variables = parent::prepareVariables();
        // Sort contributions by start date in descending order.
        usort($variables['contributions'], function($item1, $item2) {
            if ($item1['start'] == $item2['start']) {
                return 0;
            }
            return ($item1['start'] < $item2['start']) ? 1 : -1;
        });
        if (!empty($variables['organization']['url'])) {
            $variables['organization']['domain'] = parse_url($variables['organization']['url'], PHP_URL_HOST);
        }
        return $variables;
    }

}
