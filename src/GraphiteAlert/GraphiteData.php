<?php
namespace GraphiteAlert;
use Guzzle\Http\Client;

class GraphiteData
{
    protected $config;
    protected $client;

    public function __construct($config)
    {
        $this->config = $config;

        $this->client = new Client($config['url']);
    }

    public function fetch($target, $lookback)
    {
        $args = [
            'target' => $target,
            'format' => 'json',
            'from' => "-{$lookback}min"
            ];

        $request = $this->client->get('/render?'.http_build_query($args))
            ->setAuth($this->config['user'], $this->config['pass'], 'Digest');

        $r = $request->send();
        $data = $r->json();

        return $this->reformat($data);
    }

    protected function reformat($data)
    {
        $out = array();

        if (!isset($data[0]['datapoints']))
        {
            var_dump($data);
            throw new \Exception("Bad data from graphite");
        }
        foreach($data[0]['datapoints'] as $point)
        {
            if (is_null($point[0]))
                continue;
            $out[$point[1]] = $point[0];
        }

        return $out;
    }
}
