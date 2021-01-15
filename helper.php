<?php
/**
 * @file
 * Use helper commands to track contributions.
 */

require_once 'vendor/autoload.php';

use Contributions\Console\Command\GenerateCommand;
use Symfony\Component\Console\Application;

$console = new Application();
$console->add(new GenerateCommand);
$console->run();
