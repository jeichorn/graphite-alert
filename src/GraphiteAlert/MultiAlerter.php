<?php
namespace GraphiteAlert;

class MultiAlerter
{
    protected $alerters;
    public function __construct($alerters)
    {
        $this->alerters = $alerters;
    }

    public function trigger($key, $args)
    {
        foreach($this->alerters as $alerter)
            $alerter->trigger($key, $args);
    }

    public function resolve($key, $args)
    {
        foreach($this->alerters as $alerter)
            $alerter->resolve($key, $args);
    }
}
