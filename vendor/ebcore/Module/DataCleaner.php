<?php

namespace ebcore\Module;

class DataCleaner
{
    function parse_raw_http_request(array &$a_data)
    {
        $input = file_get_contents('php://input');
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;
            if (strpos($block, 'application/octet-stream') !== FALSE)
            {
                preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
            }
            else
            {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }
        return $a_data;
    }
}