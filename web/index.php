<?php
$start = microtime(true);

require_once __DIR__.'/../app/Svi/Application.php';
\Svi\Application::run();

/*print round((microtime(true) - $start) * 1000);
print 'ms : ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . 'mb';*/