<?php

namespace ContribLog\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Twig related actions.
 */
trait TwigRenderTrait {

    /**
     * Renders twig template to output.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *   Output object.
     * @param string $template_name
     *   Name of the twig template to use.
     * @param array $variables
     *   Variables to pass to twig render.
     *
     * @throws \Exception
     *   Cannot find the template.
     */
    protected function twigRender(OutputInterface $output, $template_name, $variables) {
        $candidate_template_files = ["templates/$template_name", "vendor/adaptdk/contrib/templates/$template_name"];
        foreach ($candidate_template_files as $template_file) {
            if (file_exists($template_file)) {
                $directory = dirname($template_file);
                break;
            }
        }
        if (empty($directory)) {
            throw \Exception('Cannot find a valid "templates" directory with the needed template.');
        }
        $loader = new FilesystemLoader($directory);
        $twig = new Environment($loader, [
            'cache' => 'cache',
        ]);
        $output->write($twig->render($template_name, $variables));
    }

}
