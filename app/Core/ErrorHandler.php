<?php
class ErrorHandler {
    public static function register() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        // Handle fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($level, $message, $file, $line) {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function handleException($exception) {
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }

        http_response_code($code);

        $debug = Env::get('APP_DEBUG', false);

        if ($debug) {
            self::renderDebug($exception);
        } else {
            self::renderGeneric($code);
        }
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }

    private static function renderDebug($e) {
        // Needs View class, but we are in Core. Index requires it.
        // Assuming View is available because index.php requires it.
        // But if error happens BEFORE View is required, we fallback to plain echo.

        if (class_exists('View')) {
            View::render('errors/debug', ['e' => $e], 'front');
        } else {
            // Fallback plain HTML
            echo "<div style='font-family:sans-serif; background:#0f172a; color:#f8fafc; padding:2rem;'>";
            echo "<h1 style='color:#ef4444'>CRITICAL ERROR (Before Boot)</h1>";
            echo "<h2>" . htmlspecialchars($e->getMessage()) . "</h2>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        }
    }

    private static function renderGeneric($code) {
        if (class_exists('View')) {
             if ($code == 404) {
                 View::render('errors/404', [], 'front');
             } else {
                 View::render('errors/500', [], 'front');
             }
        } else {
             echo "<h1>Error $code</h1><p>Something went wrong.</p>";
        }
    }
}
