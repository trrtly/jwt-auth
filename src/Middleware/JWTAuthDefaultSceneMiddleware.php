<?php
/**
 * Created by PhpStorm.
 * User: liyuzhao
 * Date: 2019-08-01
 * Time: 22:32
 */
namespace Phper666\JWTAuth\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Util\JWTUtil;
use Phper666\JWTAuth\PathMatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Exception\TokenValidException;

/**
 * jwt token 校验的中间件，校验场景是否一致
 * Class JWTAuthDefaultSceneMiddleware
 * @package Phper666\JWTAuth\Middleware
 */
class JWTAuthDefaultSceneMiddleware implements MiddlewareInterface
{
    /**
     * @var HttpResponse
     */
    protected $response;

    protected $jwt;

    public function __construct(HttpResponse $response, JWT $jwt, PathMatch $pathMatch)
    {
        $this->response = $response;
        $this->jwt = $jwt;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 判断是否为noCheckRoute
        $path = $request->getUri();
        $method = $request->getMethod();
        if ($this->jwt->matchRoute('default', $method, $path)) {
            return $handler->handle($request);
        }

        $token = $request->getHeaderLine('Authorization') ?? '';
        if ($token == "") {
            throw new JWTException('Missing token', 400);
        }
        $token = JWTUtil::handleToken($token);
        if ($token !== false && $this->jwt->verifyTokenAndScene('default', $token)) {
            return $handler->handle($request);
        }

        throw new TokenValidException('Token authentication does not pass', 400);
    }
}
