<?php

namespace Soarce\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Soarce\Mvc\WebApplicationController;
use Soarce\Receiver\CoverageReceiver;
use Soarce\Receiver\TraceReceiver;

class ReceiveController extends WebApplicationController
{
    public function index(Request $request, Response $response): Response
    {
        $json = json_decode((string)$request->getBody(), JSON_OBJECT_AS_ARRAY);
        if (null === $json) {
            return $response->withStatus(400);
        }

        if (!isset($json['header'], $json['payload'])) {
            return $response->withStatus(412);  //  precondition failed
        }

        /* * / //debug
        $sql = "INSERT INTO soarce.dump (raw, header, payload) VALUES ('"
            . mysqli_real_escape_string($this->ci->mysqli, (string)$request->getBody())
            . '\', \''
            . mysqli_real_escape_string($this->ci->mysqli, json_encode($json['header'], JSON_PRETTY_PRINT))
            . '\', \''
            . mysqli_real_escape_string($this->ci->mysqli, json_encode($json['payload'], JSON_PRETTY_PRINT))
            . '\');';

        $this->ci->mysqli->query($sql);
        /* */


        if ($json['header']['type'] === 'coverage') {
            $coverageReceiver = $this->container->get(CoverageReceiver::class);
            $coverageReceiver->persist($json);
        }

        if ($json['header']['type'] === 'trace') {
            $traceReceiver = $this->container->get(TraceReceiver::class);
            $traceReceiver->persist($json);
        }

        return $response;
    }
}
