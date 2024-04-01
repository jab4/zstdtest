
# zstd Output Buffer Compression Benchmark in PHP

Just heard the news that Chrome is rolling now with support for "Content-Encoding: zstd". Whoa! Finally.

Now lets put some pressure on the not-so-more innovative folk at Mozilla/Firefox.

Anyhow, here are my

## Results

```
  # none......:  4,039,229 Bytes;  100 requests in   3.16 secs  (avg=0.0316 s per req)
  # gzip......:  1,290,963 Bytes;  100 requests in  17.65 secs  (avg=0.1765 s per req)
  # obgz......:  1,290,963 Bytes;  100 requests in  17.27 secs  (avg=0.1727 s per req)
  # zstd1.....:  1,392,549 Bytes;  100 requests in   4.01 secs  (avg=0.0401 s per req)
  # zstd2.....:  1,310,025 Bytes;  100 requests in   4.32 secs  (avg=0.0432 s per req)
  # zstd3.....:  1,240,800 Bytes;  100 requests in   4.45 secs  (avg=0.0445 s per req)
  # zstd4.....:  1,222,309 Bytes;  100 requests in   4.76 secs  (avg=0.0476 s per req)
  # zstd5.....:  1,195,749 Bytes;  100 requests in   5.84 secs  (avg=0.0584 s per req)
  # zstd6.....:  1,157,140 Bytes;  100 requests in   6.94 secs  (avg=0.0694 s per req)
  # zstd7.....:  1,133,898 Bytes;  100 requests in   8.18 secs  (avg=0.0818 s per req)
  # zstd8.....:  1,120,307 Bytes;  100 requests in   9.71 secs  (avg=0.0971 s per req)
  # zstd9.....:  1,115,600 Bytes;  100 requests in   9.80 secs  (avg=0.0980 s per req)
  # zstd10....:  1,095,873 Bytes;  100 requests in  12.33 secs  (avg=0.1233 s per req)
  # zstd11....:  1,082,522 Bytes;  100 requests in  18.20 secs  (avg=0.1820 s per req)
  # zstd12....:  1,081,317 Bytes;  100 requests in  21.91 secs  (avg=0.2191 s per req)
  # zstd13....:  1,059,720 Bytes;  100 requests in  52.40 secs  (avg=0.5240 s per req)
  # zstd14....:  1,044,099 Bytes;  100 requests in  67.91 secs  (avg=0.6791 s per req)
  # zstd15....:  1,036,078 Bytes;  100 requests in  87.37 secs  (avg=0.8737 s per req)
  # zstd16....:    983,006 Bytes;  100 requests in 103.78 secs  (avg=1.0378 s per req)
  # zstd17....:    980,923 Bytes;  100 requests in 115.09 secs  (avg=1.1509 s per req)
  # zstd18....:    972,316 Bytes;  100 requests in 141.62 secs  (avg=1.4162 s per req)
  # zstd19....:    971,972 Bytes;  100 requests in 145.05 secs  (avg=1.4505 s per req)
  # zstd20....:    971,972 Bytes;  100 requests in 146.45 secs  (avg=1.4645 s per req)
  # zstd21....:    971,972 Bytes;  100 requests in 148.13 secs  (avg=1.4813 s per req)
  # zstd22....:    971,962 Bytes;  100 requests in 152.34 secs  (avg=1.5234 s per req)
```
```PHP
  /* zstd: */   ob_start(function(&$data) { return zstd_compress($data, $level); });
  /* gzip: */   ob_start(function(&$data) { return gzencode($data); });
  /* obgz: */   ob_start('ob_gzhandler');
  /* none: */   ob_start(); # No compression with default config, i.e. zlib.output_compression = Off 
```

Source document is a 4 MB long HTML file, Project Gutenberg's rendition of [War and Peace](https://www.gutenberg.org/files/2600/2600-h/2600-h.htm), 
served thru PHP script `war_and_peace.php` which applies output buffer compression as requested.

### Interpretation

- gzip handling via the built-in `ob_gzhandler` equals manually calling `gzencode`.
- gzip is horribly slow and should be dumped from production servers. Feed bad clients raw uncompressed stuff, if you don't need to pay for traffic.
- zstd levels 3 (default) .. 10 outperform gzip both time-wise and compression-wise
- zstd levels 1 and 2 don't really matter

### Requirements

- A web server
- PHP
- The great [zstd extension](https://github.com/kjdev/php-ext-zstd) by @kjdev 

### How to run

Example call:
```shell
php8.3 benchmark.php "https://localhost/zstdtest/war_and_peace.php?compression="
```
The `compression=` is left blank, as it's fed by benchmark.php during runtime.

The target, `war_and_peace.php`, contains the following:
```PHP
$comp = $_GET['compression'];
# ...
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
```
So it basically only spits out what it's being asked for, by means of the _?compression_ parameter.

### Test system (2024-Apr-01): 
- Debian sid (6.7.9-amd64 #1 SMP PREEMPT_DYNAMIC Debian 6.7.9-2 (2024-03-13) x86_64 GNU/Linux)
- Apache 2.58
- PHP 8.3.4 (cli) (built: Mar 29 2024 05:24:33)
- PHP: Server API: FPM/FastCGI
- PHP zstd extension 0.13.3
- FPM is configured in a lousy wfm-style
- CPU: AMD Ryzen 7 5700G with Radeon Graphics (family: 0x19, model: 0x50, stepping: 0x0)

# License
- MIT. Do however you please.
- Test document "War and Peace" licensed under the (Project Gutenberg license)[https://www.gutenberg.org/policy/license.html].

