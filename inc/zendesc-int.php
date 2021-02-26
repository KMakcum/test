<?php
 require 'vendor/autoload.php';

use Zendesk\API\HttpClient as ZendeskAPI;

class ZenDeskIntegration
{
    private static $_instance = null;
    public $subdomain;
    public $username;
    public $token;
    public $client;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init()
    {
        $this->token = 'ga80DjqHW2yLY3B1IAeY3lWx19NJ8VVF8pSzFoRU';
        $this->subdomain = 'lifechef';
        $this->username = 's.aslanian@solutionfactory.ru';
        $this->client = new ZendeskAPI($this->subdomain, $this->username);
        $this->client->setAuth('basic', ['username'=>$this->username,'token'=>$this->token]);
    }
}
