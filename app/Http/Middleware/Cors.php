<?php
namespace App\Http\Middleware;
use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->header('Access-Control-Allow-Origin','*'); 
        $response->header('Access-Control-Allow-Methods','GET, POST, OPTIONS, PUT, DELETE');
        $response->header('Access-Control-Allow-Headers','Accept, Authorization, Origin, Content-Type');
        $response->header('Access-Control-Max-Age','604800'); 

        return $response;
    }
}
