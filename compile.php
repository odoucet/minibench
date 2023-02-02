<?php

// Transform csv to html page with Plotly

$results = file('results.csv');

$domain = parse_url($results[0], PHP_URL_HOST);
$date = date('Y-m-d', filemtime('results.csv'));

$htmlFile = <<<HTML
<head>
    <title>Benchmark $domain - $date</title>
	<script src='https://cdn.plot.ly/plotly-2.18.0.min.js'></script>
</head>

<body>
    <h1>$domain - $date</h1>
	<div id='myDiv'><!-- Plotly chart will be drawn inside this DIV --></div>
    <script>
HTML;

// write trace
$i = 0;
$traceList = [];
foreach ($results as $line) {
    $line = explode(';', $line);
    $url = parse_url($line[0])['path'];
    $times = implode(',', array_map('floatval', array_slice($line, 1)));
    $htmlFile .= <<<HTML
    var trace$i = {
        x: [$times],
        boxpoints: 'all',
        jitter: 0.3,
        pointpos: -1.8,
        name: '$url',
        orientation: 'h',
        type: 'box'
    };

HTML;
    $traceList[] = "trace$i";
    $i++;
}

$traceListStr = implode(', ', $traceList);
$htmlFile .= <<<HTML
    var data = [$traceListStr];
    var layout = {
        xaxis: {
            title: 'Response time (in seconds)',
            zeroline: false,
        },
        showlegend: false,
        legend: {"orientation": "h"},
        yaxis: {"visible": false}
    };
    Plotly.newPlot('myDiv', data, layout);

    </script>
    </body>
    </html>
HTML;
file_put_contents('results.html', $htmlFile);
