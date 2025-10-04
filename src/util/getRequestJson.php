<?php

/**
 * Returns the requested json, and converts
 * it as php assoc array
 *
 * @return array
 */
function getRequestJson():array{
    return json_decode(
        file_get_contents('php://input'),
        true
    );
}