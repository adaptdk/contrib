<?php
/**
 * @file
 * Use helper commands to track contributions.
 */

require_once 'vendor/autoload.php';

use Contributions\Console\Command\AddCommand;
use Contributions\Console\Command\GenerateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Command\LintCommand;

$console = new Application();
$console->add(new AddCommand);
$console->add(new LintCommand);
$console->add(new GenerateCommand);
$console->run();
