<?php

namespace ContribLog\Console\Command;

use Laminas\Feed\Writer\Feed;
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
        $variables = $this->prepareVariables();
        $authors = $variables['people'];
        $projects = $variables['projects'];
        $organization = $variables['organization'];
        $contribution_types = $variables['types'];

        $feed = new Feed;
        $feed->setTitle(sprintf("%s's Open Source Contributions", $this->get('organization.name', $variables)));
        $feed->setLink($this->get('config.html.url', $variables));
        $feed->setDescription($this->get('organization.introduction', $variables));
        $feed->setFeedLink($this->get('config.rss.url', $variables), 'rss');
        $feed->setDateModified(time());
        $feed->setLanguage($this->get('config.language', $variables));

        foreach ($variables['contributions'] as $contribution) {
            $entry = $feed->createEntry();
            $entry->setTitle($contribution['title']);
            $entry->setDateCreated($contribution['start']);
            $entry->setDateModified($contribution['start']);
            $tag = sprintf('tag:%s,%s:%s', $organization['domain'], $contribution['start']->format('Y-m-d'), \urlencode($contribution['title']));
            $entry->setId($tag);
            $entry->addAuthor([
                'name'  => $authors[$contribution['who']],
            ]);
            $description = sprintf('<p>By <em>%s</em> for <a href="%s">%s</a>.</p>
            <p>%s</p>',
              $authors[$contribution['who']],
              $projects[$contribution['project']]['url'],
              $projects[$contribution['project']]['name'],
              $contribution['description']
            );
            if (!empty($contribution['links'])) {
                $links = '';
                foreach ($contribution['links'] as $link) {
                    $links = sprintf('<li><a href="%s">%s</a></li>', $link, $link);
                }
                $description .= sprintf('<ul>%s</ul>', $links);
            }
            $description = sprintf('<article>%s</article>', $description);
            $entry->setDescription($description);
            $entry->setContent($description);
            $entry->addCategory([
                'term' => $contribution_types[$contribution['type']],
            ]);
            foreach ($projects[$contribution['project']]['tags'] as $tag) {
                $entry->addCategory([
                    'term' => $tag,
                ]);
            }
            $feed->addEntry($entry);
        }

        $output->write($feed->export('rss'));
    }

}
