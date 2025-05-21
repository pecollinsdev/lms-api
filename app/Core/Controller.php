<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [])
    {
        extract($data);
        $viewPath = dirname(__DIR__, 2) . "/app/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        require_once $viewPath;
    }
}
