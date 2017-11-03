<?php

namespace Svi\Service;

use Svi\Application;
use Svi\Exception\AccessDeniedHttpException;
use Svi\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpService
{
    private $app;
    private $request;
    private $response;
    private $route;
    private $before = [];
    private $after = [];
    private $finish = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        if (!$app->isConsole() && !$app['debug']) {
            $app->error(function (NotFoundHttpException $e, Request $request) {
                ob_start();
                include __DIR__ . '/./HttpService/404.php';
                $content = ob_get_contents();
                ob_end_clean();

                return new Response($content, 404);
            });
            $app->error(function (AccessDeniedHttpException $e, Request $request) {
                ob_start();
                include __DIR__ . '/./HttpService/403.php';
                $content = ob_get_contents();
                ob_end_clean();

                return new Response($content, 403);
            });
            $app->error(function (\Throwable $e) {
                ob_start();
                include __DIR__ . '/./HttpService/500.php';
                $content = ob_get_contents();
                ob_end_clean();

                return new Response($content, 500);
            });
        }
    }

    public function run()
    {
        $this->request = Request::createFromGlobals();
        $this->route = $this->app->getRoutingService()->dispatchUrl(explode('?', $this->request->getRequestUri())[0]);

        foreach ($this->before as $before) {
            if ($this->response = $before($this->request, $this->route)) {
                break;
            }
        }

        if (!$this->response && $this->route) {
            $controller = new $this->route['controller']($this->app);
            $this->response = call_user_func_array([$controller, $this->route['method']], array_merge($this->route['args'], [$this->request]));
        }

        if (!$this->response) {
            throw new \Exception('Controller must return a response');
        }

        if (!($this->response instanceof Response)) {
            $this->response = new Response($this->response);
        }

        $this->response->prepare($this->request);

        foreach ($this->after as $after) {
            if ($result = $after($this->request, $this->response)) {
                if (!($result instanceof Response)) {
                    $this->response = new Response($result);
                }
                $this->response = $result;
                $this->response->prepare($this->request);
            }
        }

        $this->response->send();

        foreach ($this->finish as $finish) {
            $finish($this->request, $this->response);
        }
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return $this->route;
    }

    public function before($callback, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->before, $callback);
        } else {
            $this->before[] = $callback;
        }
    }

    public function after($callback, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->after, $callback);
        } else {
            $this->after[] = $callback;
        }
    }

    public function finish($callback, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->finish, $callback);
        } else {
            $this->finish[] = $callback;
        }
    }

}