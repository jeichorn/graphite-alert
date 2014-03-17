<?php
namespace GraphiteAlert;

use SimpleMail;

class EmailAlerter
{
    protected $options;
    protected $config;
    protected $type;
    public function __construct($options, $type)
    {
        $this->options = $options;
        $this->config = $options[$type];
        $this->type = strtoupper($type);
    }

    public function trigger($key, $args)
    {
        if ($this->options === false)
            return;

        $args['type'] = $this->type;
        $args['action'] = 'TRIGGERED';
        $subject = str_replace(
            array_map(function ($i) { return '{'.$i.'}'; }, array_keys($args)),
            array_values($args),
            $this->options['subject']
        );

        $msg = str_replace(
            array_map(function ($i) { return '{'.$i.'}'; }, array_keys($args)),
            array_values($args),
            $this->config['trigger']
        );

        $this->mail($subject, $msg);
    }

    public function resolve($key, $args)
    {
        if ($this->options === false)
            return;

        $args['type'] = $this->type;
        $args['action'] = 'RESOLVED';
        $subject = str_replace(
            array_map(function ($i) { return '{'.$i.'}'; }, array_keys($args)),
            array_values($args),
            $this->options['subject']
        );
        $msg = str_replace(
            array_map(function ($i) { return '{'.$i.'}'; }, array_keys($args)),
            array_values($args),
            $this->config['resolve']
        );
        $this->mail($subject, $msg);
    }

    protected function mail($subject, $msg)
    {
        $mailer = new SimpleMail();
        $mailer
            ->setTo($this->options['to'], '')
            ->setFrom($this->options['from'], '')
            ->setSubject($subject)
            ->setMessage($msg)
            ->send();
    }
}
