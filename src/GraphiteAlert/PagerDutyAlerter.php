<?php
namespace GraphiteAlert;

use PagerDuty\PagerDutyEvent;

class PagerDutyAlerter
{
    protected $options;
    public function __construct($options, $type)
    {
        $this->api = new PagerDutyEvent($options['key']);
        $this->options = $options[$type];
    }

    public function trigger($key, $args)
    {
        if ($this->options === false)
            return;
        $this->api->trigger($key, str_replace(
            array_map(function ($i) { return '{'.$i.'}'; }, array_keys($args)),
            array_values($args),
            $this->options['description']
        ));
    }

    public function resolve($key, $args)
    {
        if ($this->options === false)
            return;
        $this->api->resolve($key, str_replace(
            array_map(function ($i) { return '{'.$i.'}'; }, array_keys($args)),
            array_values($args),
            $this->options['description']
        ));
    }
}
