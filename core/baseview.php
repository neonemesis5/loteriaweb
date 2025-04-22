<?php
namespace Core;

class BaseView {
    public static function render($view, $data = []) {
        $viewFile = __DIR__ . "/../views/{$view}.php";
        if (file_exists($viewFile)) {
            extract($data);
            include $viewFile;
        } else {
            echo "Vista no encontrada: $viewFile";
        }
    }
}
