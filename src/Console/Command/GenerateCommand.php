<?php

namespace Contributions\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
        return $variables;
    }

}
