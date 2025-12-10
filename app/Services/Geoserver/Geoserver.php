<?php

namespace App\Services\Geoserver;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Geoserver
{
    const WFS_VERSION = '2.0.0'; // 1.1.0

    private $client;

    public function __construct(protected ?string $username = null, protected ?string $password = null, protected ?string $url = null)
    {
        $this->username = $username ?: config('services.geoserver.username');
        $this->password = $password ?: config('services.geoserver.password');
        $this->url = $this->url ?: config('services.geoserver.url');

        $this->client = $this->client();
    }

    public static function make(?string $username = null, ?string $password = null, ?string $url = null)
    {
        return new static($username, $password, $url);
    }

    protected function client()
    {
        return Http::baseUrl((string) Str::of($this->url)->finish('/'))
            ->withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->when(app()->isLocal() || app()->hasDebugModeEnabled(), function ($client) {
                $client->withoutVerifying();
            });
    }

    protected function get(array $request)
    {
        $default = [
            'service' => 'wfs',
            'version' => self::WFS_VERSION,
            'outputFormat' => 'application/json',
        ];

        try {
            $client = $this->client->get('wfs', array_merge($default, $request));

            if (! $client->ok()) {
                throw Exceptions\Error::serviceRespondedWithAnError($client->body());
            }

            return $client->json();
        } catch (ClientException $exception) {
            throw Exceptions\Error::clientError($exception);
        } catch (\Exception $exception) {
            throw Exceptions\Error::unexpectedException($exception);
        }
    }

    public function getDescribeFeatureType(array $request)
    {
        $default = [
            'request' => 'DescribeFeatureType',
        ];

        return $this->get(array_merge($default, $request));
    }

    public function getFeature(array $request)
    {
        $default = [
            'request' => 'GetFeature',
        ];

        return $this->get(array_merge($default, $request));
    }

    public function getFeatureId(string $id, array $request)
    {
        $default = [
            'request' => 'GetFeature',
            'featureID' => $id,
            // 'CQL_FILTER' => "IN('$id')",
        ];

        return $this->get(array_merge($default, $request));
    }

    public function getFeatureNop(string $id, array $request)
    {
        $default = [
            'request' => 'GetFeature',
            'CQL_FILTER' => 'd_nop='.$id,
        ];

        return $this->get(array_merge($default, $request));
    }

    public function transaction($data, array $request, bool $asResponse = false)
    {
        $default = [
            'service' => 'wfs',
            'version' => self::WFS_VERSION,
            'request' => 'Transaction',
            'outputFormat' => 'application/json',
            'exceptions' => 'application/json',
        ];

        try {
            $client = $this->client->withBody($data, 'text/xml')
                ->withHeaders(['Content-Type' => 'text/xml'])
                ->withQueryParameters(array_merge($default, $request))
                ->post('wfs');

            if (! $client->ok()) {
                throw Exceptions\Error::serviceRespondedWithAnError($client->body());
            }

            $isJson = str_contains($client->header('Content-Type'), 'application/json');
            $result = $isJson ? $client->json() : $client->body();

            return $asResponse
                ? ($isJson ? new JsonResponse($result, $client->status()) : new Response($result, $client->status(), ['Content-Type' => $client->header('Content-Type')]))
                : $result;
        } catch (ClientException $exception) {
            throw Exceptions\Error::clientError($exception);
        } catch (\Exception $exception) {
            throw Exceptions\Error::unexpectedException($exception);
        }
    }
}
