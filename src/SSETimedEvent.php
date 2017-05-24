<?php

namespace Todstoychev\SSE;

/**
 * SSETimedEvent
 *
 * @package Todstoychev\SSE
 * @author Todor Todorov <todstoychev@gmail.com>
 **/
class SSETimedEvent extends SSEEvent
{
    public $period = 1;
    private $start = 0;

    public function check()
    {
        if ($this->start === 0) {
            $this->start = time();
        }
        if (SSEUtils::timeMod($this->start, $this->period) == 0) {
            return true;
        } else {
            return false;
        }
    }
}