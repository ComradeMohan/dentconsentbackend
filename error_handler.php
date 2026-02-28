<?php
/**
 * Error Handler for DentConsent Backend
 * Provides a prefix/premium identification system for errors.
 * Automatically detects if the request should return HTML or JSON.
 */

// Register the error handler
set_error_handler("handleError");

// Register the exception handler
set_exception_handler("handleException");

// Register a shutdown function to catch Fatal Errors (like missing files)
register_shutdown_function("handleFatalError");

/**
 * Get human readable error name
 */
function getErrorName($errno) {
    $errors = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Fatal Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Fatal Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Fatal Error',
        E_DEPRECATED => 'Deprecated Warning',
        E_USER_DEPRECATED => 'User Deprecated Warning'
    ];
    return $errors[$errno] ?? "PHP Error ($errno)";
}

/**
 * Handle standard PHP errors
 */
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    renderErrorResponse(getErrorName($errno), $errstr, $errfile, $errline);
    exit;
}

/**
 * Handle Uncaught Exceptions
 */
function handleException($exception) {
    renderErrorResponse("Uncaught Exception", $exception->getMessage(), $exception->getFile(), $exception->getLine());
    exit;
}

/**
 * Handle Fatal Errors (e.g., missing vendor/autoload.php)
 */
function handleFatalError() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        renderErrorResponse("System Fatal Error", $error['message'], $error['file'], $error['line']);
    }
}

/**
 * Identify the error and suggest fixes
 */
function getTroubleshootingHint($message) {
    if (strpos($message, 'vendor/autoload.php') !== false) {
        return "Critical dependency missing. Please run <code>composer install</code> in the backend directory.";
    }
    if (strpos($message, 'db_connect.php') !== false || strpos($message, 'PDO') !== false) {
        return "Database connectivity issue. Check your MySQL server status and <code>config.php</code> credentials.";
    }
    if (strpos($message, 'permission denied') !== false) {
        return "File permission issue. Ensure the server has write access to the <code>uploads/</code> directory.";
    }
    return "Check the error details below for more information.";
}

/**
 * Render the error response (HTML or JSON)
 */
function renderErrorResponse($type, $message, $file, $line) {
    // Determine if we should return HTML or JSON
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    // Check if browser or explicitly requested HTML
    $isHtmlRequest = (strpos($acceptHeader, 'text/html') !== false) || (strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Mozilla') !== false);
    
    // Clean the file path for privacy
    $cleanFile = basename($file);

    if ($isHtmlRequest) {
        // Clear any previous output to ensure a clean UI
        if (ob_get_length()) ob_clean();
        
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            http_response_code(500);
        }
        
        $hint = getTroubleshootingHint($message);
        displayHtmlError($type, $message, $cleanFile, $line, $hint);
    } else {
        // Default to JSON for Android app
        if (ob_get_length()) ob_clean();
        
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'error_type' => $type,
            'message' => $message,
            'file' => $cleanFile,
            'line' => $line
        ]);
    }
}

/**
 * Display a beautiful, premium HTML error page
 */
function displayHtmlError($type, $message, $file, $line, $hint) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>System Issue Identified - DentConsent</title>
        <style>
            :root {
                --primary: #3b82f6;
                --primary-dark: #1e3a8a;
                --danger: #ef4444;
                --bg: #0f172a;
                --glass: rgba(255, 255, 255, 0.05);
                --glass-border: rgba(255, 255, 255, 0.1);
            }
            body {
                margin: 0;
                padding: 0;
                font-family: 'Inter', -apple-system, system-ui, sans-serif;
                background-color: var(--bg);
                color: #f8fafc;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                overflow: hidden;
            }
            .mesh {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: 
                    radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(239, 68, 68, 0.1) 0%, transparent 50%);
                z-index: -1;
            }
            .container {
                max-width: 600px;
                width: 90%;
                background: var(--glass);
                backdrop-filter: blur(12px);
                border: 1px solid var(--glass-border);
                border-radius: 24px;
                padding: 40px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .icon {
                width: 64px;
                height: 64px;
                background: rgba(239, 68, 68, 0.1);
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 24px;
            }
            .icon svg {
                width: 32px;
                height: 32px;
                color: var(--danger);
            }
            h1 {
                font-size: 28px;
                font-weight: 700;
                margin: 0 0 12px 0;
                background: linear-gradient(to right, #fff, #94a3b8);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            p.description {
                color: #94a3b8;
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 32px;
            }
            .alert-box {
                background: rgba(239, 68, 68, 0.05);
                border-left: 4px solid var(--danger);
                padding: 20px;
                border-radius: 12px;
                margin-bottom: 32px;
            }
            .alert-box h3 {
                margin: 0 0 8px 0;
                color: var(--danger);
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .alert-box p {
                margin: 0;
                color: #fca5a5;
                font-family: 'JetBrains Mono', 'Fira Code', monospace;
                font-size: 14px;
                word-break: break-all;
            }
            .troubleshoot {
                background: rgba(59, 130, 246, 0.05);
                border: 1px solid rgba(59, 130, 246, 0.2);
                padding: 20px;
                border-radius: 16px;
                margin-bottom: 32px;
            }
            .troubleshoot h4 {
                margin: 0 0 8px 0;
                color: #60a5fa;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .troubleshoot p {
                margin: 0;
                color: #94a3b8;
                font-size: 14px;
                line-height: 1.5;
            }
            code {
                background: rgba(0, 0, 0, 0.3);
                padding: 2px 6px;
                border-radius: 4px;
                color: #fff;
            }
            .footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 12px;
                color: #475569;
                border-top: 1px solid var(--glass-border);
                padding-top: 24px;
            }
            .badge {
                padding: 4px 12px;
                background: var(--glass-border);
                border-radius: 99px;
                color: #94a3b8;
            }
        </style>
    </head>
    <body>
        <div class="mesh"></div>
        <div class="container">
            <div class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.34c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h1><?php echo htmlspecialchars($type); ?></h1>
            <p class="description">We caught an issue that prevents the system from working correctly. Don't worry, we've identified it for you.</p>
            
            <div class="alert-box">
                <h3>Error Message</h3>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>

            <div class="troubleshoot">
                <h4>
                    <svg style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                    Recommended Action
                </h4>
                <p><?php echo $hint; ?></p>
            </div>

            <div class="footer">
                <div>Location: <span class="badge"><?php echo htmlspecialchars($file); ?>:<?php echo $line; ?></span></div>
                <div>DentConsent System Handler</div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
