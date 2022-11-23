<?php
$translate = [];
foreach (array_diff(scandir(__DIR__ . '/lang/'), ['.','..','.gitkeep']) as $translateFile) {
    if (file_exists($path = __DIR__ . '/lang/' . $translateFile)) include_once $path;
}