<?php

declare(strict_types=1);

namespace ClaudeCli;

use Anthropic\Client;
use ClaudeCli\Config\Config;

class Application
{
    public function __construct()
    {
        $config = new Config('', '');
        $client = new Client(
            apiKey: getenv('ANTHROPIC_API_KEY') ?: $config->getKey()
);
        $message = $client->messages->create(
  maxTokens: 1024,
  messages: [['role' => 'user', 'content' => 'Hello, Claude']],
  model: $config->getModel(),
);

var_dump($message->content);
    }

    public function getKey(){
        //return $this->config->getKey();
    }

    public function message($message){
       // $this->client->messages->create(
         //   messages: [
        //        [
        //            'role' => 'user',
       //             'content' => $text
       //         ]
       //     ],
       ////// );
    }
}
