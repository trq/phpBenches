<?php

if (isset($_GET['debug'])) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    define('START_TIME', microtime(true));
    define('START_MEMORY_USAGE', memory_get_usage());
}
