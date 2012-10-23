<?php
/**
 * @package     Xhprof
 * @author      Axel Etcheverry <axel@etcheverry.biz>
 * @copyright   Copyright (c) 2012 Axel Etcheverry (https://twitter.com/euskadi31)
 * @license     http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @namespace
 */
namespace Xhprof\Profiler;

class Report
{
    /**
     * @var Array
     */
    protected $allowMetrics = array(
        "wt" => array(
            "Wall",
            "microsecs",
            "walltime"
        ),
        "ut" => array(
            "User",
            "microsecs",
            "user cpu time"
        ),
        "st" => array(
            "Sys",
            "microsecs",
            "system cpu time"
        ),
        "cpu" => array(
            "Cpu",
            "microsecs",
            "cpu time"
        ),
        "mu" => array(
            "MUse",
            "bytes",
            "memory usage"
        ),
        "pmu" => array(
            "PMUse",
            "bytes",
            "peak memory usage"
        ),
        "samples" => array(
            "Samples",
            "samples",
            "cpu time"
        )
    );

    /**
     * @var Array
     */
    protected $metrics = array();

    /**
     * @var Array
     */
    protected $data = array(
        'legends' => array(
            "fn"            => "Function Name",
            "ct"            => "Calls",
            "ct_pct"        => "Calls (%)",

            "wt"            => "Incl. Wall Time (ms)",
            "iwall_pct"     => "IWall (%)",
            "excl_wt"       => "Excl. Wall Time (ms)",
            "ewall_pct"     => "EWall (%)",

            "ut"            => "Incl. User (ms)",
            "iuser_pct"     => "IUser (%)",
            "excl_ut"       => "Excl. User (ms)",
            "euser_pct"     => "EUser (%)",

            "st"            => "Incl. Sys (ms)",
            "isys_pct"      => "ISys (%)",
            "excl_st"       => "Excl. Sys (ms)",
            "esys_pct"      => "ESys (%)",

            "cpu"           => "Incl. CPU (ms)",
            "icpu_pct"      => "ICpu (%)",
            "excl_cpu"      => "Excl. CPU (ms)",
            "ecpu_pct"      => "ECPU (%)",

            "mu"            => "Incl. MemUse (bytes)",
            "imuse_pct"     => "IMemUse (%)",
            "excl_mu"       => "Excl. MemUse (bytes)",
            "emuse_pct"     => "EMemUse (%)",

            "pmu"           => "Incl. PeakMemUse (bytes)",
            "ipmuse_pct"    => "IPeakMemUse (%)",
            "excl_pmu"      => "Excl. PeakMemUse (bytes)",
            "epmuse_pct"    => "EPeakMemUse (%)",

            "samples"       => "Incl. Samples",
            "isamples_pct"  => "ISamples (%)",
            "excl_samples"  => "Excl. Samples",
            "esamples_pct"  => "ESamples (%)"
        ),
        'overall' => array(),
        'metrics' => array()
    );

    public function __construct($data)
    {
        $this->_getMetrics($data);

        $this->data['metrics'] = $this->_computeInclusiveTimes($data);

        /**
         * total metric value is the metric value for "main()"
         */
        foreach($this->metrics as $metric) {
            $this->data['overall'][$metric] = $this->data['metrics']['main()'][$metric];
        }

        /*
         * initialize exclusive (self) metric value to inclusive metric value
         * to start with.
         * In the same pass, also add up the total number of function calls.
         */
        foreach($this->data['metrics'] as $symbol => $info) {
            foreach($this->metrics as $metric) {
                $this->data['metrics'][$symbol]["excl_" . $metric] = $this->data['metrics'][$symbol][$metric];
                $this->data["metrics"][$symbol]['fn'] = $symbol;
            }

            if(!isset($this->data['overall']['ct'])) {
                $this->data['overall']['ct'] = 0;
            }
            $this->data["overall"]["ct"] += $info["ct"];
        }


        /**
         * adjust exclusive times by deducting inclusive time of children
         */
        foreach($data as $parentChild => $info) {
            list($parent, $child) = $this->_parseParentChild($parentChild);

            if($parent) {
                foreach($this->metrics as $metric) {
                    // make sure the parent exists hasn't been pruned.
                    if(isset($this->data['metrics'][$parent])) {
                        $this->data['metrics'][$parent]["excl_" . $metric] -= $info[$metric];
                    }
                }
            }
        }

        foreach($this->data['metrics'] as $symbol => $info) {

            $this->data['metrics'][$symbol]['wt_pct'] = (
                ($this->data['metrics'][$symbol]['wt'] / abs($this->data['overall']['wt'])) * 100
            );

            $this->data['metrics'][$symbol]['ct_pct'] = (
                ($this->data['metrics'][$symbol]['ct'] / abs($this->data['overall']['ct'])) * 100
            );

            $this->data['metrics'][$symbol]['ewall_pct'] = (
                ($this->data['metrics'][$symbol]['excl_wt'] / abs($this->data['overall']['wt'])) * 100
            );
        }

        //print_r($this->data);

        /*
        foreach($data as $key => $metric) {
            //$this->total += $metric['wt'];
            $this->calls += $metric['ct'];

            if($key == 'main()') {
                $this->total = $metric['wt'];
            }
        }

        foreach($data as $key => $metric) {
            $part = $this->_parseParentChild($key);

            $ret = array(
                'parent'        => $part[0],
                'name'          => $part[1],
            );

            foreach($this->allowMetrics as $metricKey => $metricValue) {

            }

            $this->metrics[] = array(
                'parent'        => $part[0],
                'name'          => $part[1],
                'ct'            => $metric['ct'],
                'ct_percent'    => (($metric['ct'] / $this->calls) * 100),
                'wt'            => $metric['wt'],
                'wt_percent'    => (($metric['wt'] / $this->total) * 100)
            );
        }*/
    }

    public function getLegend($symbol)
    {
        if(isset($this->data['legends'][$symbol])) {
            return $this->data['legends'][$symbol];
        }

        throw new \InvalidArgumentException(sprintf('Symbol %s does not exist.', $symbol));
    }

    protected function _getMetrics($data)
    {
        foreach($this->allowMetrics as $metric => $desc) {
            if(isset($data['main()'][$metric])) {
                $this->metrics[] = $metric;
            }
        }
    }

    protected function _computeInclusiveTimes($data)
    {
        //$metrics = $this->_getMetrics($data);

        $symbolTab = array();

        foreach($data as $parentChild => $info) {
            list($parent, $child) = $this->_parseParentChild($parentChild);

            if($parent == $child) {
                throw new \RuntineException(sprintf('Error in Raw Data: parent & child are both: %s', $parent));
            }

            if(!isset($symbolTab[$child])) {
                $symbolTab[$child] = array(
                    "ct" => $info['ct']
                );

                foreach($this->metrics as $metric) {
                    $symbolTab[$child][$metric] = $info[$metric];
                }
            } else {
                $symbolTab[$child]["ct"] += $info["ct"];
                /* update inclusive times/metric for this child  */
                foreach($this->metrics as $metric) {
                    $symbolTab[$child][$metric] += $info[$metric];
                }
            }
        }

        return $symbolTab;
    }

    public function sortBy($key)
    {
        $sorter = array();
        $ret = array();

        reset($this->data['metrics']);

        foreach($this->data['metrics'] as $i => $val) {
            $sorter[$i] = $val[$key];
        }

        arsort($sorter);

        foreach($sorter as $i => $val) {
            $ret[$i] = $this->data['metrics'][$i];
        }
        $this->data['metrics'] = $ret;
        unset($ret);

        return $this;
    }

    public function _parseParentChild($parentChild)
    {
        $ret = explode("==>", $parentChild);

        // Return if both parent and child are set
        if (isset($ret[1])) {
            return $ret;
        }

        return array(null, $ret[0]);
    }

    public function getMetrics()
    {
        return $this->data['metrics'];
    }

    public function getOverall()
    {
        return $this->data['overall'];
    }
}
