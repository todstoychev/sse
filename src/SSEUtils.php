<?php

namespace Todstoychev\SSE;

/**
 * Class SSEUtils
 */
class SSEUtils
{
    /**
     * @param string $str
     *
     * @return string
     */
    public static function sseData(string $str): string
    {
        return 'data: '.str_replace("\n", "\ndata: ", $str);
    }

    /**
     * @param string $id
     * @param string $event
     * @param string $data
     */
    public static function sseBlock(string $id, string $event, string $data)
    {
        echo 'id: '.$id."\n";

        if ($event != '') {
            echo 'event: '.$event."\n";
        }

        echo self::sseData($data)."\n\n";//send the data
    }

    /**
     * @param float $start
     * @param int $n
     *
     * @return int
     */
    public static function timeMod(float $start, int $n)
    {
        return (time() - $start) % $n;
    }

    /**
     * @param int $start
     *
     * @return int
     */
    public static function timeDiff(int $start)
    {
        return time() - $start;
    }
}
