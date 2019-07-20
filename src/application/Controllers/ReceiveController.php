<?php

namespace Soarce\Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;

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
        /*
        print_r($this->ci->database);

        $sql = 'INSERT INTO soarce.dump (payload) VALUES ("'
            . mysqli_real_escape_string($this->ci->database, (string)$request->getBody())
            . '");';
        */

        /** @var array $requestData ['header' => ..., 'payload' => ...] */
        $requestData = json_decode((string)$request->getBody(), JSON_OBJECT_AS_ARRAY);

        $sql = "INSERT INTO soarce.dump (raw, header, payload) VALUES ('"
            . mysqli_real_escape_string($this->ci->database, (string)$request->getBody())
            . '\', \''
            . mysqli_real_escape_string($this->ci->database, json_encode($requestData['header'], JSON_PRETTY_PRINT))
            . '\', \''
            . mysqli_real_escape_string($this->ci->database, json_encode($requestData['payload'], JSON_PRETTY_PRINT))
            . '\');'; /* */

        $this->ci->database->query($sql);

        return $response;
	}
}
