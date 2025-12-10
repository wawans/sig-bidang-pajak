<?php

namespace App\Support\Response;

use Illuminate\Contracts\Support\Responsable;

class ApiResponse implements Responsable
{
    /**
     * The underlying resource.
     *
     * @var mixed
     */
    public $resource;

    /**
     * Create a new resource response.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Create a new response instance.
     *
     * @param  mixed  ...$parameters
     * @return static
     */
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }

    public static function data($data = [], $message = '', $status = 'success')
    {
        return new static([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function fail($message = '', $errors = [], $status = 'error')
    {
        return new static([
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
        ]);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toJsonResponse($request)
    {
        return response()->json($this->resource);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResourceResponse($request)
    {
        return response()->json([
            'page' => $this->resource->currentPage(),
            'per_page' => $this->resource->perPage(),
            'total_page' => $this->resource->lastPage(),
            'data' => $this->resource->jsonSerialize(),
            'total' => $this->resource->total(),
        ]);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return $this->resource instanceof \Illuminate\Http\Resources\Json\JsonResource
            ? $this->toResourceResponse($request)
            : $this->toJsonResponse($request);
    }
}
