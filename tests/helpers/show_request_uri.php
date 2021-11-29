<?php

ini_set("display_errors", false);
require_once dirname(__FILE__, 3) . '/lib/limo.php';
echo request_uri() . "\n";