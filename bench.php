<?php

// load urls in urls.txt
$urlRaw = file('urls.txt');

// remove trailing newlines
$urlRaw = array_map('trim', $urlRaw);
$urls = [];

// copy URLs 10 times:
for ($i = 0; $i < 10; $i++) {
    $urls = array_merge($urlRaw, $urls);
}

// shuffle URLs
shuffle($urls);

// bench
echo "Starting benchmark...\n";
$results = [];

$i = 0;
foreach ($urls as $url) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    $end = microtime(true);
    $results[$url][] = $end - $start;
    $avancement = $i/count($urls)*100;
    printf("[%2d %%] %5.3f %-60s\r", $avancement, $end-$start, $url);

    $i++;

    // wait a little
    usleep(100000);
}

foreach ($results as $url => $timing) {
    file_put_contents('results.csv', sprintf("%s;%s\n", $url, implode(';', $timing)), FILE_APPEND);
}
// debug
file_put_contents('results.serialize', serialize($results));
echo "Done!";
