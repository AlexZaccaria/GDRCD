<?php

class JsonResponder {

    function responde($response) {
        header('Content-Type: application/json');
        $response['session_id'] = session_id();
        $encoded_respone = serialize($response);
        echo json_encode($response);
    }

}