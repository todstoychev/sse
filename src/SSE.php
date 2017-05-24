<?php

namespace Todstoychev\SSE;

use Exception;

class SSE
{
    /**
     * @var array
     */
    private $handlers = [];

    /**
     * @var int Event id
     */
    private $id = 0;

    /**
     * @var float
     */
    public $sleepTime = 0.5;

    /**
     * @var int
     */
    public $execLimit = 600;

    /**
     * @var int
     */
    public $clientReconnect = 1;

    /**
     * @var bool
     */
    public $allowCors = false;

    /**
     * @var int
     */
    public $keepAliveTime = 300;

    /**
     * @var bool
     */
    public $isReconnect = false;

    /**
     * @var bool
     */
    public $useChunkedEncoding = false;

    public function __construct()
    {
        if (array_key_exists('HTTP_LAST_EVENT_ID', $_SERVER) && !empty($_SERVER['HTTP_LAST_EVENT_ID'])) {
            $this->id = intval($_SERVER['HTTP_LAST_EVENT_ID']);
            $this->isReconnect = true;
        }
    }

    /**
     * @param $event
     * @param $handler
     *
     * @return \Todstoychev\SSE\SSE
     * @throws \Exception
     */
    public function addEventListener(string $event, $handler)
    {
        if ($handler instanceof SSEEvent) {
            $this->handlers[$event] = $handler;
        } else {
            throw new Exception('An event handler must be an instance of SSEEvent.');
        }

        return $this;
    }

    /**
     * @param string $event
     *
     * @return \Todstoychev\SSE\SSE
     */
    public function removeEventListener(string $event)
    {
        unset($this->handlers[$event]);

        return $this;
    }


    public function start()
    {
        @set_time_limit(0);//disable time limit

        //send the proper header
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        if ($this->allowCors) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: true');
        };

        if ($this->useChunkedEncoding) {
            header('Transfer-encoding: chunked');
        }

        //prevent buffering
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }

        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);

        while (ob_get_level() != 0) {
            ob_end_flush();
        }

        ob_implicit_flush(1);
        $start = time();//record start time
        echo 'retry: '.($this->clientReconnect * 1000)."\n";    //set the retry interval for the client

        //keep the script running
        while (true) {
            if (SSEUtils::timeMod($start, $this->keepAliveTime) == 0) {
                //No updates needed, send a comment to keep the connection alive.
                //From https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
                echo ': '.sha1(mt_rand())."\n\n";
            }

            //start to check for updates
            foreach ($this->handlers as $event => $handler) {
                if (!$handler->check()) {
                    continue;
                }

                $data = $handler->update();//get the data
                $this->id++;
                SSEUtils::sseBlock($this->id, $event, $data);
                //make sure the data has been sent to the client
                @ob_flush();
                @flush();
            }

            //break if the time excceed the limit
            if ($this->execLimit != 0 && SSEUtils::timeDiff($start) > $this->execLimit) {
                break;
            }

            usleep($this->sleepTime * 1000000);
        }
    }
}

;
