<?php
namespace Jellyfish;
/**
 * A recreation of the Response class found in Laravel
 *
 * @package Jellyfish
 * @author  Michael Brose <michael.brose@jellyfish.net>
 */

class Response
{
    public $statusTexts = [
        200 => 'OK',
        400 => 'Bad Request',
    ];
    /**
     * Returns an HTTP response in a JSON format
     *
     * @access public
     * @param mixed $content Data that will be encoded into JSON
     * @param int $status The HTTP status
     */
    public function json($content, $status = 200) {
        header('HTTP/1.1 '.$status.' '.$this->statusTexts[$status]);
        header('Content-type: application/json');
        echo json_encode($content);
        exit;
    }
}

/**
 * Global funciton to quickly create a response object
 */
function response() {
    return new \Jellyfish\Response();
}