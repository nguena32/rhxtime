<?php
// vendor/autoload.php
// Autoloader manuel pour PHPMailer (installé manuellement car Composer est absent)

require_once __DIR__ . '/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/SMTP.php';
