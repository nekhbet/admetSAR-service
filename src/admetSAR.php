<?php

namespace Nekhbet\admetSARService;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nekhbet\admetSARService\Exceptions\admetSARException;
use Psr\Http\Message\ResponseInterface;

class admetSAR
{
    private string $endpoint = 'http://lmmd.ecust.edu.cn/admetsar2/result/';

    private array $userAgentsPool = [
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14.2; rv:109.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];

    private string $userAgent = '';
    private string $SMILES_code = '';
    private int $requestTimeout;
    private int $sleepBetweenJobResultsRetry = 5; // In seconds

    public function __construct(int $requestTimeout = 20)
    {
        $this->requestTimeout = $requestTimeout;
        $this->userAgent      = $this->userAgentsPool[rand(0, count($this->userAgentsPool) - 1)];
    }

    public function setUserAgent(string $ua): admetSAR
    {
        $this->userAgent = $ua;

        return $this;
    }

    /**
     * @throws admetSARException
     */
    public function setSMILESCode(string $SMILES): admetSAR
    {
        $this->SMILES_code = $SMILES;
        if (strlen($SMILES) < 8) {
            throw new admetSARException("SMILES too short");
        }

        return $this;
    }

    /**
     * @throws GuzzleException
     */
    private function doGetCall(string $url, array $get_data): ResponseInterface
    {
        $client = new Client([
            'timeout' => $this->requestTimeout,
        ]);

        return $client->request('GET', $url, [
                'headers'         => [
                    'User-Agent' => $this->userAgent,
                ],
                'allow_redirects' => true,
                'query'           => $get_data,
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    private function doPostCall(string $url, array $post_data): ResponseInterface
    {
        $client = new Client([
            'timeout' => $this->requestTimeout,
        ]);

        return $client->request('POST', $url, [
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
                'json'    => $post_data,
            ]
        );
    }

    /**
     * @throws GuzzleException
     * @throws admetSARException
     */
    public function submitJob(): int
    {
        // Validate input
        if ( ! $this->SMILES_code) {
            throw new admetSARException("SMILES Code not set!");
        }

        $replyStage1 = $this->doPostCall($this->endpoint, [
            'smis'      => [$this->SMILES_code],
            'endpoints' => 'all',
        ]);

        if ($replyStage1->getStatusCode() !== 200) {
            throw new admetSARException("SubmitJob: Invalid Status Code: ".$replyStage1->getStatusCode());
        }
        $contentStage1 = $replyStage1->getBody()->getContents();
        $json          = json_decode($contentStage1);
        if ( ! isset($json->result) || ! is_numeric($json->result)) {
            throw new admetSARException("SubmitJob: Invalid Content");
        }

        return intval($json->result);
    }

    /**
     * @throws admetSARException
     * @throws GuzzleException
     */
    public function parseJobResults(int $job_id, bool $wait_to_finish = true): array
    {
        $response = [
            'status' => 'unknown',
            'data'   => [],
        ];

        // Retrieve the results page
        $replyStage2 = $this->doGetCall($this->endpoint, [
            'tid'  => $job_id,
            'type' => 'compound',
        ]);

        if ($replyStage2->getStatusCode() !== 200) {
            throw new admetSARException("JobResults: Invalid Status Code: ".$replyStage2->getStatusCode());
        }

        $content = trim($replyStage2->getBody()->getContents());
        if (stripos($content, 'false') !== false) {
            if ($wait_to_finish === false) {
                // Throw exception as
                $response['status'] = 'not_finished';

                return $response;
            }
            // Otherwise ... retry it in a loop
            sleep($this->sleepBetweenJobResultsRetry);

            return self::parseJobResults($job_id, $wait_to_finish);
        }

        $json = json_decode($content, JSON_OBJECT_AS_ARRAY);

        if ( ! $json || ! isset($json['predictions'])) {
            throw new admetSARException("JobResults: Invalid Content");
        }

        // Content is not in waiting state anymore ... parse it, or try at least :)
        $response['data']   = [
            'predictions' => $json['predictions']['compound1'],
            'properties'  => $json['profiles']['compound1'],
            'regressions' => $json['regressions']['compound1'],
        ];
        $response['status'] = 'parsed';

        return $response;
    }

    private function dd(): void
    {
        $args = func_get_args();
        call_user_func_array('print_r', $args);
        die();
    }

}
