<?php

const AUTOLOAD_PATH = __DIR__ . '/vendor/autoload.php';
const DD_HELPER_PATH = __DIR__ . '/vendor/larapack/dd/src/helper.php';

if (file_exists(AUTOLOAD_PATH)) {
    require_once AUTOLOAD_PATH;
}

if (file_exists(DD_HELPER_PATH)) {
    require_once DD_HELPER_PATH;
}