<?php

namespace Benches;

class Bench
{
    private $url;
    private $abRequests;
    private $abConcurreny;

    private $data = [
        'memory'            => 0,
        'render_time'       => 0,
        'included'          => 0,
        'requestsPerSecond' => 0,
        'functionCalls'     => 0,
        'loadAverage'       => 0
    ];

    public function __construct($url, $abRequests = 3000, $abConcurrency = 100)
    {
        $this->url           = $url;
        $this->abRequests    = $abRequests;
        $this->abConcurrency = $abConcurrency;
    }

    public static function run($url, $restartServer = false, $waitForLoadAverage = false)
    {
        $bench = new static($url);

        if ($restartServer) {
            $bench->restartServer();
        }

        if ($waitForLoadAverage) {
            $bench->waitForLoadAverage();
        }

        return $bench->primeCache()->request();
    }

    public function restartServer($overload = null)
    {
        if ($overload === null) {
            shell_exec("sudo /etc/init.d/apache2 restart");
        } else {
            $callback();
        }

        return $this;
    }

    public function waitForLoadAverage($sleep = 60, $average = 0.05, Callable $overload = null)
    {
        if ($overload === null) {
            do {
                sleep($sleep);
                $loadavg = strstr(shell_exec('cat /proc/loadavg'), ' ', true);
            } while ($loadavg > $average);
        } else {
            $overload();
        }

        return $this;
    }

    public function primeCache()
    {
        shell_exec("curl -X GET \"{$this->url}\""); usleep(300000);
        return $this;
    }

    public function request()
    {
        $o = shell_exec("curl -X GET --ignore-content-length \"{$this->url}/?debug=1\"");

        if (preg_match("/<!-- (.*?) -->/", $o, $match)) {
            $this->data = json_decode($match[1]);
        }

        $o = shell_exec("ab -n {$this->abRequests} -c {$this->abConcurrency} -H \"Connection: close\" \"{$this->url}\"");
        if (preg_match("/Requests\ per\ second:\ +(.*?)\[/", $o, $mat)) {
            $this->data['loadAverage'] = strstr(shell_exec('cat /proc/loadavg'), ' ', true);
            $this->data['requestsPerSecond'] = $mat[1];
        }

        return $this;
    }
}

if (strtolower(basename($_SERVER['SCRIPT_NAME'])) == strtolower(basename(__FILE__))) {
    if ($argc == 2) {
        Bench::run($argv[1]);
    } else {
        echo "Usage {$argv[0]} <benchmark url>\n";
    }
}
