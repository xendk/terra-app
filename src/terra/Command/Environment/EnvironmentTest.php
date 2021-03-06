<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;
use terra\Command\Command;
use terra\Factory\EnvironmentFactory;

class EnvironmentTest extends Command
{
    protected function configure()
    {
        $this
        ->setName('environment:test')
        ->setDescription('Run tests on the environment.')
        ->addArgument(
            'app_name',
            InputArgument::OPTIONAL,
            'The name the app.'
        )
        ->addArgument(
            'environment_name',
            InputArgument::OPTIONAL,
            'The name the environment.'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello Terra!');

        // Ask for an app and environment.
        $this->getApp($input, $output);
        $this->getEnvironment($input, $output);

        $environment_factory = new EnvironmentFactory($this->environment, $this->app);

        $environment_factory->getConfig();

        // Run the tests
        // @TODO: Move to factory.
        $output->writeln('<info>TERRA</info> | <comment>Test: Start...</comment>');
        $output->writeln('<info>TERRA</info> | ' . $environment_factory->config['hooks']['test']);

        // Set environment variables for behat tests
        $env = array();
        $env['HOME'] = $_SERVER['HOME'];

        $behat_vars = array(
          'extensions' => array(
            'Behat\MinkExtension' => array(
              'base_url' => 'http://'.$environment_factory->getUrl(),
            ),
          ),
        );

        // @TODO: This is NOT WORKING.  We MUST figure out how to override the base_url.
        $env['BEHAT_PARAMS'] = json_encode($behat_vars);

        $process = new Process($environment_factory->config['hooks']['test'], $environment_factory->getSourcePath(), $env);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo $buffer;
            } else {
                echo $buffer;
            }
        });

        if (!$process->isSuccessful()) {
            $output->writeln('<info>TERRA</info> | <fg=red>Test Failed</> '.$hook);
        } else {
            $output->writeln('<info>TERRA</info> | <info>Test Passed: </info> '.$hook);
        }
        $output->writeln('');
    }
}
