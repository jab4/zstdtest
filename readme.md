
# zstd Output Buffer Compression Benchmark in PHP

Just heard the news that Chrome is rolling now with support for "Content-Encoding: gzip". Whoa! Finally.

Now lets put some pressure on the not-so-more innovative folk at Mozilla/Firefox.

Anyhow, here are my

## Results

### Test system: 
- Debian sid (6.7.9-amd64 #1 SMP PREEMPT_DYNAMIC Debian 6.7.9-2 (2024-03-13) x86_64 GNU/Linux)
- Apache 2.58
- PHP 8.3.4 (cli) (built: Mar 29 2024 05:24:33)
- PHP: Server API: FPM/FastCGI
- PHP zstd extension 0.13.3
- FPM is configured in a lousy wfm-style
- CPU: AMD Ryzen 7 5700G with Radeon Graphics (family: 0x19, model: 0x50, stepping: 0x0)