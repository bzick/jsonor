<?php

namespace Jsonor;

use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class JSON {

    /**
     * Encode data to JSON
     * @param mixed $data
     * @param bool $pretty
     *
     * @return string
     */
    public static function encode($data, $pretty = true) {
        return json_encode($data, ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Decode JSON string
     * @param string $json
     *
     * @return Container|mixed
     * @throws ParsingException
     * @throws null
     */
    public static function decode($json) {
        $data = json_decode($json, true);
        if($data === null && $error = json_last_error()) {
            if($error == JSON_ERROR_SYNTAX || $error == JSON_ERROR_UTF8) {
                $parser = new JsonParser();
                throw $parser->lint($json);
            } elseif(function_exists('json_last_error_msg')) {
                throw new ParsingException(json_last_error_msg());
            } else {
                throw new ParsingException("Failed to parse JSON");
            }
        } elseif(is_array($data)) {
            return new Container($data);
        } else {
            return $data;
        }
    }

    public static function lint($json) {
        $parser = new JsonParser();
        return $parser->lint($json);
    }
}