<?php

if (!function_exists('zstd_compress')) {
    exit("ERROR: Function zstd_compress does not exist.");
}

ini_set('error_reporting', E_ALL);

if (empty($_GET['compression'])) {
    exit("Bad param for ?compression.");
}
$comp = $_GET['compression'];

preg_match('%(\d+)%', substr($comp, 0, 16), $level);
if (!empty($level[1]) && ($level = intval($level[1])) && (0 === strpos($comp, 'zstd'))) {
    $comp = 'zstd';
} else {
    $level = null;
}

switch ($comp) {
    case 'zstd':
        header("Content-Encoding: zstd");
        ob_start(function(&$data) use ($level) {
            return zstd_compress($data, $level);
        });
        break;
    case 'gzip':
        header("Content-Encoding: gzip");
        ob_start(function(&$data) {
            return gzencode($data);
        });
        break;
    case 'obgz':
        ob_start('ob_gzhandler');
        break;
    default:
        # nothing
}

readfile(__DIR__.'/war_and_peace.html');
