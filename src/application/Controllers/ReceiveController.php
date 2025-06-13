<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Mvc\WebApplicationController;
use Soarce\QueueManager;

class ReceiveController extends WebApplicationController
{
    public function index(Request $request, Response $response): Response
    {
        /* * / //debug
        $json = json_decode((string)$request->getBody(), JSON_OBJECT_AS_ARRAY);
        if (null === $json) {
            return $response->withStatus(400);
        }

        if (!isset($json['header'], $json['payload'])) {
            return $response->withStatus(412);  //  precondition failed
        }

        $mysqli = $this->container->get(\mysqli::class);
        $sql = "INSERT INTO soarce.dump (raw, header, payload) VALUES ('"
            . mysqli_real_escape_string($mysqli, (string)$request->getBody())
            . '\', \''
            . mysqli_real_escape_string($mysqli, json_encode($json['header'], JSON_PRETTY_PRINT))
            . '\', \''
            . mysqli_real_escape_string($mysqli, json_encode($json['payload'], JSON_PRETTY_PRINT))
            . '\');';

        $mysqli->query($sql);
        /* */

        $queueManager = $this->container->get(QueueManager::class);
        $queueManager->store((string)$request->getBody());

        return $response->withStatus(201); // created
    }
}
