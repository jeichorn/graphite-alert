<?php
namespace GraphiteAlert;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MonitorCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('monitor')
            ->setDescription('Poll graphite based on configured alerts')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Just print alerts, don't send them to pagerduty")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();

        $config = include __DIR__."/../../config.php";

        $alterers = [
            'alert' => new MultiAlerter([
                        new PagerDutyAlerter($config['pagerduty'], 'alert'),
                        new EmailAlerter($config['email'], 'alert'),
                       ]),
            'warn' => new MultiAlerter([
                        new PagerDutyAlerter($config['pagerduty'], 'warn'),
                        new EmailAlerter($config['email'], 'warn'),
                       ]),
        ];

        $options['graphite_url'] = $config['graphite']['url'];

        $metrics = $this->processTemplates($config['metrics']);

        $graphite = new GraphiteData($config['graphite']);
        $monitor = new GraphiteMonitor($output, $alterers, $graphite, $metrics, $options);


        $output->writeln("<info>Found ".count($metrics)." metrics</info>");
        foreach(array_keys($metrics) as $metric)
            $output->writeln("<info>    $metric</info>");

        $monitor->monitor();
    }

    //@todo refactor this
    protected function processTemplates($metrics)
    {
        foreach($metrics as $metric => $config)
        {
            if (isset($config['template']))
            {
                unset($metrics[$metric]);
                foreach($config['keys'] as $key)
                {
                    foreach($config['template'] as $m => $c)
                    {
                        $m = sprintf($m, $key);
                        $c['target'] = sprintf($c['target'], $key);
                        $metrics[$m] = $c;
                    }
                }
            }
        }

        return $metrics;
    }
}
