<?php
/**
 * Created by PhpStorm.
 * User: Sajjad
 * Date: 02/7/18
 * Time: 11:42 AM
 */

namespace App\Repository\Services;


use App\Exceptions\GeneralException;
use App\Project;
use App\Scenario;
use App\Thing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\CurlService;

class CoreService
{
    protected $base_url;
    protected $port;
    protected $dmPort;
    protected $pmPort;
    protected $downLinkPort;
    protected $curlService;

    public function __construct(CurlService $curlService)
    {
        $this->base_url = config('iot.core.serverBaseUrl');
        $this->pmPort = config('iot.core.pmPort');
        $this->downLinkPort = config('iot.core.downLinkPort');
        $this->curlService = $curlService;
    }

    /**
     * General send function for core services that handles errors
     * @param $url
     * @param $data
     * @param string $method
     * @param string $port
     * @return array|object
     * @throws GeneralException
     */
    private function _send($url, $data, $method, $port)
    {
        $url = $this->base_url . ':' . $port . $url;

        $response = $this->curlService->to($url)
            ->withTimeout(100)
            ->withData($data)
            ->withOption('SSL_VERIFYHOST', false)
            ->returnResponseObject()
            ->asJsonRequest()
            ->asJsonResponse()
            ->withTimeout('60');
        $new_response = null;
        switch ($method) {
            case 'get':
                $new_response = $response->get();
                break;
            case 'post':
                $new_response = $response->post();
                break;
            case 'delete':
                $new_response = $response->delete();
                break;
            default:
                $new_response = $response->get();
                break;
        }
        /*
        Log::debug('-----------------------------------------------------');
        Log::debug(print_r($data, true));
        Log::debug(print_r($new_response, true));
        Log::debug('-----------------------------------------------------');
        */

        if ($new_response->status == 0) {
            throw new GeneralException('Core service connection error', 500);
        }
        if ($new_response->status == 200) {
            return $new_response->content ?: [];
        }
        if ($new_response->status != 200) {
            if ($new_response->content) {
                throw new GeneralException(
                    $new_response->content->message ?: 'Unknown Core service error',
                    $new_response->status
                );
            } else {
                throw new GeneralException('Core service connection error', $new_response->status);
            }
        }
    }

    /**
     * @param Project $project
     * @param Thing $thing
     * @param $codec
     * @return array
     * @throws GeneralException
     */
    public function sendCodec(Project $project, Thing $thing, $codec)
    {
        Log::debug("Core Send Codec\t" . $project['_id']);
        $url = '/api/runners/' . $project['container']['name'] . '/api/codecs';
        $response = $this->_send($url, ['code' => $codec, 'id' => $thing['interface']['devEUI']], 'post', $this->pmPort);
        return $response;
    }

    /**
     * @param Project $project
     * @param Thing $thing
     * @param $data
     * @return array
     * @throws GeneralException
     */
    public function encode(Project $project, Thing $thing, $data)
    {
        $url = '/api/runners/' . $project['container']['name'] . '/api/codecs/' . $thing['interface']['devEUI'] . '/encode';
        $response = $this->_send($url, $data, 'post', $this->pmPort);
        return $response;
    }

    /**
     * @param Project $project
     * @param Thing $thing
     * @param $data
     * @return array
     * @throws GeneralException
     */
    public function decode(Project $project, Thing $thing, $data)
    {
        $url = '/api/runners/' . $project['container']['name'] . '/api/codecs/' . $thing['interface']['devEUI'] . '/decode';
        $response = $this->_send($url, $data, 'post', $this->pmPort);
        return $response;
    }

    /**
     * @param Project $project
     * @param Scenario $scenario
     * @return array
     * @throws GeneralException
     */
    public function sendScenario(Project $project, Scenario $scenario)
    {
        Log::debug("Core Send Scenario\t" . $project['_id']);
        $url = '/api/runners/' . $project['container']['name'] . '/api/scenarios';
        $response = $this->_send($url, ['code' => $scenario->code, 'id' => $project['container']['name']], 'post', $this->pmPort);
        return $response;
    }

    /**
     * @param Project $project
     * @param $code
     * @return array
     * @throws GeneralException
     */
    public function lint(Project $project, $code)
    {
        Log::debug("Core Lint\t" . $project['_id']);
        $url = '/api/runners/' . $project['container']['name'] . '/api/lint';
        $response = $this->_send($url, $code, 'post', $this->pmPort);
        return $response;
    }

    /**
     * @param Project $project
     * @param Thing $thing
     * @param $data
     * @param int $fport
     * @param bool $confirmed
     * @return array
     * @throws GeneralException
     */
    public function downLinkThing(Project $project, Thing $thing, $data, $fport = 2, $confirmed = false)
    {
        Log::debug("DownLink Project List\t" . $thing['dev_eui']);
        $url = '/api/send';
        $data = [
            'application_id' => $project['application_id'],
            'thing_id' => $thing['interface']['devEUI'],
            'data' => $data, 'confirmed' => $confirmed, 'fport' => (int)$fport
        ];
        $response = $this->_send($url, $data, 'post', $this->downLinkPort);
        return $response;
    }
}
