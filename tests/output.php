<?php

test_case("Output");
test_case_describe("Testing limo output functions.");
if (!defined('URL_FOR_OUTPUT_TEST')) {
    define('URL_FOR_OUTPUT_TEST', TESTS_DOC_ROOT . '02-outputs.php');
}

function before_each_test_in_output()
{
    env(null);
    option('encoding', 'utf-8');
}

function test_output_render()
{
    $lorem = "Lorem ipsum dolor sit amet.";
    $q_lorem = preg_quote($lorem);

    # Testing standard rendering with sprint string
    assert_equal(render($lorem), $lorem);
    assert_equal(render($lorem, null, ['unused']), $lorem);
    assert_equal(render("Lorem %s dolor sit amet.", null, ['ipsum']), $lorem);
    assert_equal(render("Lorem %s dolor sit amet.", null, ['var1' => 'ipsum']), $lorem);

    $response = test_request(URL_FOR_OUTPUT_TEST . '/render0');
    assert_equal($response, $lorem);
    $response = test_request(URL_FOR_OUTPUT_TEST . '/render1');
    assert_equal($response, $lorem);

    # Testing rendering with a view (inline function case)
    $view = '_test_output_html_hello_world';
    $html = render($view);
    assert_match("/Hello World/", $html);
    assert_no_match("/$q_lorem/", $html);
    $html = render($view, null, [$lorem]);
    assert_no_match("/$q_lorem/", $html);
    $html = render($view, null, ['lorem' => $lorem]);
    assert_match("/$q_lorem/", $html);

    # Testing layout option
    $layout = '_test_output_html_my_layout';
    $html = render($lorem, $layout);
    assert_match("/$q_lorem/", $html);
    assert_match("/<title>Page title<\/title>/", $html);

    # Testing layout + view (inline function case)
    $html = render($view, $layout);
    assert_match("/<title>Page title<\/title>/", $html);
    assert_match("/Hello World/", $html);
    assert_no_match("/$q_lorem/", $html);
    $html = render($view, $layout, ['lorem' => $lorem]);
    assert_match("/<title>Page title<\/title>/", $html);
    assert_match("/Hello World/", $html);
    assert_match("/$q_lorem/", $html);

    # Testing layout + view (template files case)
    $views_dir = __DIR__ . '/apps/views/';
    option('views_dir', $views_dir);

    $view = 'hello_world.html.php';
    $layout = 'layouts/default.html.php';
    $html = render($view, $layout);
    assert_match("/<title>Page title<\/title>/", $html);
    assert_match("/Hello World/", $html);
    assert_no_match("/$q_lorem/", $html);
    $html = render($view, $layout, ['lorem' => $lorem]);
    assert_match("/<title>Page title<\/title>/", $html);
    assert_match("/Hello World/", $html);
    assert_match("/$q_lorem/", $html);
}

function test_output_layout()
{
    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/layout');
    $o = <<<HTML
<html><body>
hello!</body></html>
HTML;
    assert_equal($response, $o);

    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/layout2');
    $o = <<<HTML
<html><body>
<p>my content</p>
<p>my sidebar</p>
</body></html>
HTML;
    assert_equal($response, $o);
}

function test_output_content_for()
{
    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/content_for');
    $o = <<<HTML
<html><body>
<p>my content</p>
<p>my sidebar</p>
</body></html>
HTML;
    assert_equal($response, $o);
}

function test_output_partial()
{
    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/partial');
    assert_equal($response, 'no layout there buddy');
}

function test_output_html()
{
}

function test_output_render_file()
{
    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/text', 'GET', true);
    assert_header($response, 'Content-type', 'text/plain; charset=' . option('encoding'));

    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/jpeg', 'GET', true);
    assert_header($response, 'Content-type', 'image/jpeg');

    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/unknown_page', 'GET', true);
    assert_header($response, 'Content-type', 'text/html; charset=' . option('encoding'));
}

function test_output_before_filter()
{
    function before_render($content_or_func, $layout, $locals, $view_path)
    {
        if (is_callable($content_or_func)) {
        } elseif (file_exists($view_path) && !array_key_exists('content', $locals)) {
            // a view file but not a layout
            $view_path = file_path(option('views_dir'), basename($content_or_func, ".html.php") . "_filtered.html.php");
        } else {
            # it's a string
            $content_or_func .= "∞FILTERED∞";
        }

        return [$content_or_func, $layout, $locals, $view_path];
    }

    $lorem = "Lorem ipsum dolor sit amet.";
    $html = render("Lorem %s dolor sit amet.", null, ['ipsum']);
    assert_match("/$lorem∞FILTERED∞/", $html);

    $views_dir = __DIR__ . '/apps/views/';
    option('views_dir', $views_dir);
    $view = 'hello_world.html.php';
    $layout = 'layouts/default.html.php';
    $html = render($view, $layout, ['lorem' => $lorem]);
    assert_match("/FILTERED/i", $html);
    assert_match("/$lorem/", $html);
}

function test_output_autorender()
{
    $response = test_request(TESTS_DOC_ROOT . '02-outputs.php/autorender');
    assert_equal($response, 'AUTORENDERED OUTPUT for empty_controller');
}

end_test_case();

# Views and Layouts

function _test_output_html_my_layout($vars)
{
    extract($vars, EXTR_OVERWRITE); ?>
    <html>
    <head>
        <title>Page title</title>
    </head>
    <body>
    <?php
    echo $content ?>
    </body>
    </html>
<?php
}

function _test_output_html_hello_world($vars)
{
    extract($vars, EXTR_OVERWRITE); ?>
    <p>Hello World</p>
    <?php
    if (isset($lorem)): ?><p><?php
        echo $lorem ?></p><?php
    endif; ?>
<?php
}
