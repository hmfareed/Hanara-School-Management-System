<?php
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

// Forward Vercel requests to normal index.php
require __DIR__ . '/../public/index.php';
