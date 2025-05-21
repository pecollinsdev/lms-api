<?php

namespace App\Core;

class Layout
{
    private static $sections = [];
    private static $currentSection = null;
    private static $layout = null;
    private static $content = '';
    private static $variables = [];

    public static function start($layout)
    {
        self::$layout = $layout;
        ob_start();
    }

    public static function end()
    {
        if (!self::$layout) {
            throw new \Exception('Layout not set. Call Layout::start() before Layout::end()');
        }

        // Get any remaining content
        $remainingContent = ob_get_clean();
        if (!empty($remainingContent)) {
            self::$sections['content'] = $remainingContent;
        }

        // Make sure we have content
        if (empty(self::$sections['content'])) {
            self::$sections['content'] = '';
        }
        
        extract(self::$sections);
        extract(self::$variables);
        $layoutPath = dirname(__DIR__, 2) . "/app/Views/layouts/" . self::$layout . ".php";
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout file not found: {$layoutPath}");
        }
        
        require_once $layoutPath;
    }

    public static function section($name)
    {
        // If there's any content in the buffer, store it
        if (ob_get_level() > 0) {
            $content = ob_get_clean();
            if (!empty($content)) {
                self::$sections['content'] = $content;
            }
        }
        
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection()
    {
        if (self::$currentSection) {
            $content = ob_get_clean();
            self::$sections[self::$currentSection] = $content;
            self::$currentSection = null;
        }
    }

    public static function render($name)
    {
        return self::$sections[$name] ?? '';
    }

    public static function include($view)
    {
        extract(self::$variables);
        $viewPath = dirname(__DIR__, 2) . "/app/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        require_once $viewPath;
    }

    public static function setVariable($name, $value)
    {
        self::$variables[$name] = $value;
    }
} 