<?php
//$Id: views-views-json-style-exhibit.tpl.php,v 1.1.2.4 2010/07/15 07:34:48 allisterbeharry Exp $
/**
 * @file views-views-json-style-exhibit.tpl.php
 * Default template for the Views JSON style plugin using the Simile/Exhibit format
 *
 * Variables:
 * - $view: The View object.
 * - $rows: Hierachial array of key=>value pairs to convert to JSON
 * - $options: Array of options for this style
 *
 * @ingroup views_templates
 */

$jsonp_prefix = $options['jsonp_prefix'];

if (!empty($view->live_preview)) {
  $json = _views_json_encode_formatted($rows);
  if ($jsonp_prefix) {
    $json = "$jsonp_prefix($json)";
  }
  print "<code>$json</code>";
}
else {
  $json = json_encode($rows);
  if ($jsonp_prefix) {
    $json = "$jsonp_prefix($json)";
  }
  if ($options['using_views_api_mode']) {
    // We're in Views API mode.
    print $json;
  }
  else {
    // We want to send the JSON as a server response so switch the content
    // type and stop further processing of the page.
    $content_type = ($options['content_type'] == 'default') ? 'application/json' : $options['content_type'];
    drupal_set_header("Content-Type: $content_type; charset=utf-8");
    print $json;
    //Don't think this is needed in .tpl.php files: module_invoke_all('exit');
    exit;
  }
}
