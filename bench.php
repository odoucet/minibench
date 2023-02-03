<?php

$results = [];

/***** intercept ctrl+c */
register_shutdown_function("shutdown");                  // Handle END of script

declare(ticks = 1);                                      // Allow posix signal handling
pcntl_signal(SIGINT,"shutdown");                         // Catch SIGINT, run shutdown()     

function shutdown() {
    global $results;
    echo "Shutting down, writing...\n";
    write_results($results);
    echo "Done!\n";
    exit;
}

// load urls in urls.txt
$urlRaw = file('urls.txt');

// remove trailing newlines
$urlRaw = array_map('trim', $urlRaw);
$urls = [];

// copy URLs 10 times:
$tries = ($argv[1]) ?? 10;
for ($i = 0; $i < $tries; $i++) {
    $urls = array_merge($urlRaw, $urls);
}

// shuffle URLs
shuffle($urls);

// bench
echo "Starting benchmark...\n";


$i = 0;
foreach ($urls as $url) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    $end = microtime(true);
    if (!array_key_exists($url, $results)) {
	$results[$url] = [];
	continue; // do not log first result
    }
    $results[$url][] = $end - $start;
    $avancement = $i/count($urls)*100;
    printf("[%2d %%] %5.3f %-60s\r", $avancement, $end-$start, $url);

    $i++;

    // wait a little
    usleep(100000);
}

write_results($results);
echo "Done!";

function write_results($results) {
    foreach ($results as $url => $timing) {
        file_put_contents('results.csv', sprintf("%s;%s\n", $url, implode(';', $timing)), FILE_APPEND);
    }
    // debug
    file_put_contents('results.serialize', serialize($results));
}


