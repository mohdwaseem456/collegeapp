<?php
// src/Core/Request.php
namespace Src\Core;

class Request {

    // Get POST/JSON body as array
    public static function body(): array {
        $data = file_get_contents("php://input");
        return json_decode($data, true) ?? [];
    }

    // Get HTTP method (GET, POST, etc.)
    public static function method(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    // Get URI path, removing query string and project prefix
    public static function uri(): string {
        
        $uri = strtok($_SERVER['REQUEST_URI'], '?');

        // Replace with your project folder if needed
        $prefix = "/college/public";

        if (str_starts_with($uri, $prefix)) {
            $uri = substr($uri, strlen($prefix));
        }
          
        return $uri ?: "/";
    }
}
