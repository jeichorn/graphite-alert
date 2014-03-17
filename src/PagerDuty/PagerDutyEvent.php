<?php
namespace PagerDuty;

use Guzzle\Http\Client;
use Guzzle\Plugin\History\HistoryPlugin;
use Exception;

class PagerDutyEvent
{
        const ENDPOINT = 'https://events.pagerduty.com/generic/2010-04-15/';
        protected $key;

        public function __construct($key)
        {
            $this->key = $key;
        }

        public function trigger($key, $description, $client = '', $client_url = '', $details = [])
        {
            $this->send('trigger', $key, $description, $client, $client_url, $details);
        }

        public function resolve($key, $description, $client = '', $client_url = '', $details = [])
        {
            $this->send('resolve', $key, $description, $client, $client_url, $details);
        }

        protected function send($type, $key, $description, $client, $client_url, $details)
        {
            $payload = [
                'service_key' => $this->key,
                'event_type' => $type,
                'description' => $description,
                'incident_key' => $key,
                'client' => $client,
                'client_url' => $client_url,
                'details' => $details,
                ];

            $client = new Client(self::ENDPOINT);
            $history = new HistoryPlugin();
            $client->addSubscriber($history);
            $request = $client->post('create_event.json', null, json_encode($payload));

            $data = $request->send()->json();

            //echo $history->getLastRequest();
            var_dump($data);
        }

}
