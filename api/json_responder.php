<?php

class JsonResponder {

    function responde($response) {
        header('Content-Type: application/json');
        // uncomment the next line if you're looking for session_id injection over responses
        // $response['session_id'] = session_id();
        $encoded_respone = serialize($response);
        echo json_encode($response);
    }

}