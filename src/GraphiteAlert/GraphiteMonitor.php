<?php
namespace GraphiteAlert;

use Exception;
use Symfony\Component\Console\Output\OutputInterface;


class GraphiteMonitor
{
    protected $metrics;
    protected $options;
    protected $lookback = 10;
    protected $output;
    protected $alerter;
    protected $graphite;
    protected $db = [];

    public function __construct(OutputInterface $output, $alerter, $graphite, $metrics, $options)
    {
        $this->output = $output;
        $this->alerter = $alerter;
        $this->metrics = $metrics;
        $this->graphite = $graphite;
        $this->options = $options;

        if (file_exists('/tmp/graphite_alert.db'))
            $this->db = unserialize(file_get_contents('/tmp/graphite_alert.db'));
    }

    public function monitor()
    {
        $units = new Units();

        foreach($this->metrics as $metric => $config)
        {
            $data = $this->graphite->fetch($config['target'], $this->lookback);

            $warn_level = $units->toRaw($config['warn']);
            $alert_level = $units->toRaw($config['alert']);

            $warn = 0;
            $alert = 0;
            $bad_value = null;

            foreach($data as $ts => $value)
            {
                if ($value >= $warn_level)
                {
                    $bad_value = $value;
                    $warn++;
                }
                if ($value >= $alert_level)
                {
                    $bad_value = $value;
                    $alert++;
                }
            }

            // kinda odd to have this here instead of in the alerter code
            $args = ['width' => 586,
                'height' => 307,
                'target' => "alias({$config['target']},'$metric')",
                'from' => '-1h',
                ];
            $t1 = ['target' => "alias(threshold($warn_level),'Warn')"];
            $t2 = ['target' => "alias(threshold($alert_level),'Alert')"];
            $url = $this->options['graphite_url']."/render/?".http_build_query($args).'&'.http_build_query($t1).'&'.http_build_query($t2);

            $args = [
                'name'  => $metric, 
                'value' => $units->toUnit($config['unit'], $bad_value),
                'times' => 0,
                'lookback' => $this->lookback,
                'url'   => $url
                ];

            if ($alert > 2)
            {
                $args['times'] = $alert;
                $this->alerter['alert']->trigger($metric, $args);
                $this->info("$metric at alert level $args[value]");
                $this->mark('alert', $metric);
            }
            elseif ($warn > 2)
            {
                $args['times'] = $warn;
                $this->alerter['warn']->trigger($metric, $args);
                $this->info("$metric at warn level $args[value]");
                $this->mark('warn', $metric);
            }
            else
            {
                // all clear resolve any outstanding alerts
                if (isset($this->db[$metric]))
                {
                    $this->alerter[$this->db[$metric]]->resolve($metric, $args);
                    $this->info("$metric {$this->db[$metric]} resolved");
                    unset($this->db[$metric]);
                }
            }
        }
    }

    protected function info($msg)
    {
        $this->output->writeln("<info>$msg</info>");
    }

    protected function alert($msg)
    {
        $this->output->writeln("<error>$msg</error>");
    }

    protected function mark($type, $metric)
    {
        $this->db[$metric] = $type;
    }

    public function __destruct()
    {
        file_put_contents('/tmp/graphite_alert.db', serialize($this->db));
    }
}
