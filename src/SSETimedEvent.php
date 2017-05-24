<?php

namespace Todstoychev\SSE;

/**
 * SSETimedEvent
 **/
class SSETimedEvent extends SSEEvent
{
    /**
     * @var int
     */
    public $period = 1;

    /**
     * @var int
     */
    private $start = 0;

    public function check()
    {
        if ($this->start === 0) {
            $this->start = time();
        }

        if (SSEUtils::timeMod($this->start, $this->period) == 0) {
            return true;
        }

        return false;
    }
}