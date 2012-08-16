<?php

class Bench
{
    private $url;
    private $xhprofUrl;
    private $abRequests;
    private $abConcurreny;

    private $requestsPerSecond   = 0;
    private $memory              = 0;
    private $time                = 0;
    private $functionCalls       = 0;
    private $fileCount           = 0;
    private $loadAverage         = 0;
    private $functionMapUrl      = '';

    public function __construct($url, $xhprofUrl, $abRequests = 3000, $abConcurrency = 100)
    {
        $this->url           = $url;
        $this->xhprofUrl     = $xhprofUrl;
        $this->abRequests    = $abRequests;
        $this->abConcurrency = $abConcurrency;
    }

    public static function run($url, $xhprofUrl, $restartServer = true, $waitForLoadAverage = true)
    {
        $bench = new static($url, $xhprofUrl);

        if ($restartServer) {
            $bench->restartServer();
        }

        if ($waitForLoadAverage) {
            $bench->waitForLoadAverage();
        }

        return $bench->primeCache()->request();
    }

    public function getRequestsPerSecond()
    {
        return $this->requestsPerSecond;
    }

    public function getMemoryUsage()
    {
        return $this->memory;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getFunctionCalls()
    {
        return $this->functionCalls;
    }

    public function getFileCount()
    {
        return $this->fileCount;
    }

    public function getLoadAverage()
    {
        return $this->loadAverage;
    }

    public function getFunctionMapUrl()
    {
        return $this->requestsPerSecond;
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
        if (preg_match("/in \<b\>(.*?) ms(.*?)\<b\>(.*?) KB(.*?)files: (.*?),(.*?)\<a href=\"(.*?)\"/", $o, $mat)) {
            $this->memory    = $mat[3];
            $this->time      = $mat[1];
            $this->fileCount = $mat[5]-2;
            $o = shell_exec("curl -X GET \"" . urldecode($mat[7]) . "\"");
            if (preg_match("/Number of Function Calls(.*?)\<td\>(.*?)\<\/td/", $o, $mat2) && preg_match("/href=\"(.*?)\"\>\[View Full Callgraph/", $o, $mat3)) {
                $this->functionMapUrl = $this->xhprofUrl . '/' . $mat3[1];
                $this->functionCalls = str_replace([",", " "], ["", ""], $mat2[2]);
            }
        }

        $o = shell_exec("ab -n {$this->abRequests} -c {$this->abConcurrency} -H \"Connection: close\" \"{$this->url}/\"");
        if (preg_match("/Requests\ per\ second:\ +(.*?)\[/", $o, $mat)) {
            $this->loadAverage = strstr(shell_exec('cat /proc/loadavg'), ' ', true);
            $this->requestsPerSecond = $mat[1];
        }

        return $this;
    }
}

if (strtolower(basename($_SERVER['SCRIPT_NAME'])) == strtolower(basename(__FILE__))) {
    if ($argc == 3) {
        Bench::run($argv[1], $argv[2], false, false);
    } else {
        echo "Usage {$argv[0]} <benchmark url> <xhprof url>\n";
    }
}
