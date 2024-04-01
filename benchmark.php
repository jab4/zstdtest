<?php

error_reporting(E_ALL);

$baseURL = $argv[1] ?: 0;
if (empty($baseURL)) {
    myZstdBenchmark::printerr("Error: You must provide a base URL, ex. 'http://localhost/war_and_peace.php?compression='\n");
    exit(1);
}

myZstdBenchmark::main($baseURL);


class myZstdBenchmark {

    static $opts = ['none', 'gzip', 'obgz', 'zstd'];
    static $testCount = 100;
    static $report = [];
    static $traffic = 0;

    static function printerr() {
        return file_put_contents('php://stderr', call_user_func_array('sprintf', func_get_args()));
    }

    /**
     * @param $opt
     * @param $url
     * @return void
     */
    static function getTiming($opt, $url) {
        self::printerr("Running test for %s <%s>...", $opt, $url);
        $time = microtime(true);
        $size = NULL;
        for ($i = 0; $i < self::$testCount; $i++) {
            print '.';
            $size = strlen(file_get_contents($url, false, stream_context_create([
                "http" => [
                    'header' => "Accept-Encoding: gzip, deflate, br, zstd" # important to make ob_gzhandler work
                ],
                "ssl"=> [
                    "verify_peer" => false,      # ☢️☢️☢️ SECURITY WARNING! THIS BREAKS SSL! ONLY USE IN YOUR LAB ENV!
                    "verify_peer_name" => false, # ☢️☢️☢️ SECURITY WARNING! THIS BREAKS SSL! ONLY USE IN YOUR LAB ENV!
                ],
            ])));
            if (empty($size)) {
                printf("Error: Bad response from server (%s)\n\n", $http_response_header);
                break;
            }
        }
        $time = microtime(true) - $time;
        if ($size) {
            self::$traffic += $size * self::$testCount;
            self::$report[] = sprintf("  %-'.10s:  %9s Bytes;  %d requests in %6.2F secs  (avg=%-7.4Fs per req)\n", $opt, number_format($size), self::$testCount, $time, $time/self::$testCount);
        }
        self::printerr("\33[2K\r%s completed in %1.02F s!%s\n", $opt, $time, $size ? '' : ' (ERROR)');
    }

    /**
     * @param $baseURL
     * @return void
     */
    static function main($baseURL) {
        foreach (self::$opts as $opt) {
            if ($opt == 'zstd') {
                for ($zstdLevel = 3; $zstdLevel <= 22; $zstdLevel++) {
                    $opt = 'zstd' . $zstdLevel;
                    self::getTiming($opt, $baseURL . $opt);
                }
            } else {
                self::getTiming($opt, $baseURL . $opt);
            }
        }
        printf("Done! Results:\n\n%s\n\n", implode('', self::$report));
        printf("I caused ~%s MB of traffic in %d seconds.\n", number_format(self::$traffic/pow(1024,2), 2), time() - $_SERVER['REQUEST_TIME']);
    }

}

