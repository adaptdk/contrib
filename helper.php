<?php
/**
 * @file
 * Use helper commands to track contributions.
 */

require_once 'vendor/autoload.php';

use Contributions\Console\Command\AddCommand;
use Contributions\Console\Command\FormatCommand;
use Contributions\Console\Command\GenerateCommand;
use Contributions\Console\Command\LintCommand;
use Symfony\Component\Console\Application;

$console = new Application();
$console->add(new AddCommand);
$console->add(new FormatCommand);
$console->add(new GenerateCommand);
$console->add(new LintCommand);
$console->run();
