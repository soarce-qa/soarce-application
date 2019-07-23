<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Slim\Http\StatusCode;
use Soarce\Receiver\CoverageReceiver;

class ReceiveController
{
    /** @var Container */
    protected $ci;

    /**
     * BaseController constructor.
     *
     * @param Container $dependencyInjectionContainer
     */
    public function __construct(Container $dependencyInjectionContainer)
    {
        $this->ci = $dependencyInjectionContainer;
    }

    /**
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 */
  	public function index(Request $request, Response $response): Response
    {
        $json = json_decode((string)$request->getBody(), JSON_OBJECT_AS_ARRAY);
        if (null === $json) {
            return $response->withStatus(StatusCode::HTTP_BAD_REQUEST);
        }

        if (!isset($json['header'], $json['payload'])) {
            return $response->withStatus(StatusCode::HTTP_PRECONDITION_FAILED);
        }

        /* */ //debug
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
            $coverageReceiver = new CoverageReceiver($this->ci);
            $coverageReceiver->persist($json);
        }

/*        if ($json['header']['type'] === 'trace') {
            $coverageReceiver = new CoverageReceiver($this->ci);
            $coverageReceiver->persist($json);
        }*/

        return $response;
	}
}
