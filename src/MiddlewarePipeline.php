<?php
namespace ASB\MiddlewarePipe;


trait MiddlewarePipeline
{
    protected $middlewares = [];

    /**
     * @param array $middleware
     * @return $this
     */
    public function middleware(array $middleware)
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        return self::getStaticProxy($this,$middlewares);
    }
    public static function middlewared(array $middlware)
    {
        return self::getStaticProxy(self::class,$middlware);
    }

    /**
     * @param object $class
     * @return mixed
     */
    public static function getStaticProxy(object|string $class, $middleware)
    {
        return new class($class, $middleware)
        {
            private $callable;
            private $middlewares;
            public function __construct($callable,$middlewares)
            {
                $this->callable = $callable;
                $this->middlewares = $middlewares;
            }

            public function __call($method, $params)
            {
                $next = function ($params) use ($method) {
                    try {
                        return call_user_func_array([$this->callable, $method], $params);
                    } catch (\Throwable $e) {
                        return $e;
                    }
                };
                return is_string($this->callable)?$this->callable::sendItStaticThroughPipes($next,$params, $method,$this->middlewares):
                $this->callable->sendItThroughPipes($next,$params, $method,$this->middlewares);
            }
        };
    }
    public static function sendItStaticThroughPipes($next,$params, $method,$middlewares)
    {
        if (!method_exists(self::class, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist.");
        }

        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middlewareClass) {
                return function ($request) use ($middlewareClass, $next) {
                    $middleware = new $middlewareClass();
                    return $middleware->handle($request, $next);
                };
            },
            $next
        );
        return $pipeline($params);
    }
    public function sendItThroughPipes($next,$params, $method,$middlewares)
    {
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist.");
        }

        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middlewareClass) {
                return function ($request) use ($middlewareClass, $next) {
                    $middleware = new $middlewareClass();
                    return $middleware->handle($request, $next);
                };
            },
            $next
        );
        return $pipeline($params);
    }
}