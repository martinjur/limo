<?php
#
# Limo
# --
# Example 05: using content_for() for capturing content in a view and injecting 
# it in the layout
#
#
#

require_once dirname(__FILE__, 3) . '/lib/limo.php';

function configure()
{
    option('env', ENV_DEVELOPMENT);
}

dispatch('/', 'index');
function index()
{
    set('page_title', "using content_for()");

    return html('index.html.php', 'layout.html.php');
}

run();

