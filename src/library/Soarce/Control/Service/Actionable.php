<?php

namespace Soarce\Control\Service;

use Soarce\Config\Service;

class Actionable
{
    private const PING__EXPECTED_RESPONSE = 'pong';

    private const CHECKSUM_HEADER = 'X-SOARCE-FileChecksum';

    private const PRESHARED_SECRET_HEADER = 'X-SOARCE-Preshared-Secret';

    /** @var Service */
    private $serviceConfig;

    /** @var bool[] */
    private $preconditions;

    /**
     * Actionable constructor.
     *
     * @param Service $serviceConfig
     */
    public function __construct(Service $serviceConfig)
    {
        $this->serviceConfig = $serviceConfig;
    }

    /**
     * @return Service
     */
    public function getServiceConfig(): Service
    {
        return $this->serviceConfig;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return self::PING__EXPECTED_RESPONSE === file_get_contents($this->buildUrl('ping'), false, $this->getStreamContext());
    }

    /**
     * @return array[]
     */
    public function getDetails(): array
    {
        return json_decode(file_get_contents($this->buildUrl('details'), false, $this->getStreamContext()), JSON_OBJECT_AS_ARRAY);
    }

    /**
     *
     */
    public function collectPreconditions(): void
    {
        $this->preconditions = json_decode(file_get_contents($this->buildUrl('preconditions'), false, $this->getStreamContext()), JSON_OBJECT_AS_ARRAY);
    }

    /**
     * @return string
     */
    public function start(): string
    {
        return file_get_contents($this->buildUrl('start'), false, $this->getStreamContext());
    }

    /**
     * @return string
     */
    public function end(): string
    {
        return file_get_contents($this->buildUrl('end'), false, $this->getStreamContext());
    }

    /**
     * @return bool[]
     */
    public function getPreconditions(): array
    {
        return $this->preconditions;
    }

    /**
     * @param  string $filename
     * @return FileContent
     */
    public function getFile($filename): FileContent
    {
        $fileContent = file_get_contents($this->buildUrl('readfile') . '&' . http_build_query(['filename' => $filename]), false, $this->getStreamContext());

        $md5 = '';
        foreach ($http_response_header as $header) {
            $line = explode(':', $header, 2);
            if (count($line) !== 2) {
                continue;
            }
            if ($line[0] === self::CHECKSUM_HEADER) {
                $md5 = trim($line[1]); break;
            }
        }

        return new FileContent(
            explode("\n", $fileContent),
            $md5
        );
    }

    /**
     * @param  string $action
     * @return string
     */
    private function buildUrl($action): string
    {
        return $this->serviceConfig->getUrl()
            . '?'
            . $this->serviceConfig->getParameterName()
            . '='
            . $action;
    }

    /**
     * @return resource
     */
    private function getStreamContext()
    {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => self::PRESHARED_SECRET_HEADER . ': ' . $this->serviceConfig->getPresharedSecret(),
            ],
        ];

        return stream_context_create($opts);
    }
}
