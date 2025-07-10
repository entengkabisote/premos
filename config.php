<?php
date_default_timezone_set('Asia/Manila');

if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Local server config
    define('DB_HOST', 'localhost');
    define('DB_USER', 'premos');
    define('DB_PASS', 'DiegoRiel20!4');
    define('DB_NAME', 'premos');
} else {
    // Live server config
    define('DB_HOST', 'localhost');
    define('DB_USER', 'smscorp_premos');
    define('DB_PASS', 'DiegoRiel20!4');
    define('DB_NAME', 'smscorp_premos');
}

// SMTP config (same for both local and live, unless you want to separate)
define('SMTP_HOST', 'mail.smscorp.ph'); 
define('SMTP_USER', 'ericson.ramos@smscorp.ph'); 
define('SMTP_PASS', 'Enteng1972'); 
define('SMTP_PORT', 465); 
define('SMTP_SEC', 'ssl');
