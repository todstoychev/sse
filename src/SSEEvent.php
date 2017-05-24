<?php

namespace Todstoychev\SSE;

/**
 * Class SSEEvent
 */
class SSEEvent
{
    public function check()
    {
        //data always updates
        return true;
    }

    public function update()
    {
        //returns nothing
        return '';
    }
}

;

