<?php

namespace Benches;

use Xhprof\Profiler\Report;

class Profiler
{
    protected $debug;
    protected $start_time;
    protected $start_memory_usage;

    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    public function pre()
    {
        if ($this->debug) {
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
            $this->start_time = microtime(true);
            $this->start_memory_usage = memory_get_usage();
        }
    }

    public function post()
    {
        $data = xhprof_disable();

        if ($this->debug) {
            $json                = [];
            $json                = (new Report($data))->getOverall();
            $json['render_time'] = round((microtime(true) - $this->start_time), 5) * 1000;
            $json['memory']      = round((memory_get_usage() - $this->start_memory_usage) / 1024, 2);

            // TODO: This figure needs to be adjusted to compensate
            // for the benchmarking framework itself.
            $json['included']    = count(get_included_files());

            print '<!-- ' . json_encode($json) . ' -->';
        }
    }
}
