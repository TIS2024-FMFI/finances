<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * A middleware which ensures that a JSON response is returned if the incoming HTTP
 * request expects responses of the content type "application/json".
 */
class JsonifyResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->expectsJson() && !($response instanceof JsonResponse)) {
            return $this->jsonifyResponse($response);
        }

        return $response;
    }

    /**
     * Transform a plain-text response into a JSON response, by replacing its
     * original content with a JSON of the form:
     * [ "displayMessage": <original-content> ].
     * 
     * @param \Illuminate\Http\Response $response
     * the response to transform
     * @return \Illuminate\Http\JsonResponse
     * the resulting JSON response
     */
    private function jsonifyResponse(Response $response)
    {
        $data = [ 'displayMessage' => $response->content() ];
        $headers = $response->headers->all();
        unset($headers['content-type']);

        return response()->json($data, $response->status(), $headers);
    }
}
