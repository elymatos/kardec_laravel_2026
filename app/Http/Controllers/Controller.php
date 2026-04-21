<?php

namespace App\Http\Controllers;

use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Orkester\Manager;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected object $data;

    protected string $notify;

    protected string $hx_trigger;

    public function __construct(
        protected readonly Request $request
    ) {
        //        $this->data = Manager::getData();
        // $this->data->currentUrl = $request->getCurrentUrl() ?? '/' . $request->path();
        $this->notify = '';
        $this->hx_trigger = '';
    }

    #[Get(path: '/empty')]
    public function empty()
    {
        $response = response('', 200);

        return $response;
    }

    public function render(string $view, ?string $fragment = null)
    {
        //        $this->data = Manager::getData();
        if (str_contains($view, '.')) {
            $viewName = $view;
        } else {
            $class = get_called_class();
            $viewName = str_replace('\\', '.', str_replace('Controller', '', str_replace('App\\Http\\Controllers\\', '', $class))).".{$view}";
        }
        $vars = get_object_vars($this->data);
        if (is_null($fragment)) {
            $response = response()
                ->view($viewName, $vars);
        } else {
            $response = view($viewName, $vars)->fragment($fragment);
        }
        if ($this->notify != '') {
            $response->header('HX-Trigger', $this->notify);
        }
        if ($this->hx_trigger != '') {
            $response->header('HX-Trigger', $this->hx_trigger);
        }

        return $response;
    }

    public function clientRedirect(string $url)
    {
        $response = response();

        return response('')
            ->withHeaders([
                'HX-Redirect' => $url,
            ]);
    }

    public function redirect(string $url)
    {
        return response('')
            ->withHeaders([
                'HX-Redirect' => $url,
            ]);
    }

    public function notify($type, $message): string
    {
        //        HX-Trigger: {"showMessage":"Here Is A Message"}
        $this->notify = json_encode([
            'notify' => [
                'type' => $type,
                'message' => $message,
            ],
        ]);

        return $this->notify;
    }

    public function trigger(string $trigger)
    {
        $this->hx_trigger = $trigger;
    }

    public function renderNotify($type, $message)
    {
        if ($this->hx_trigger != '') {
            $trigger = json_encode([
                'notify' => [
                    'type' => $type,
                    'message' => $message,
                ],
                $this->hx_trigger => [],
            ]);
        } else {
            $trigger = $this->notify($type, $message);
        }
        $response = response('', 204)->header('HX-Trigger', $trigger);

        return $response;
    }
}
