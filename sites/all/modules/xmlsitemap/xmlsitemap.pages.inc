<?php

/**
 * @file
 * Page callbacks for the xmlsitemap module.
 *
 * @ingroup xmlsitemap
 */

/**
 * Get the sitemap chunk/page of the current request.
 */
function xmlsitemap_get_current_chunk(stdClass $sitemap) {
  // Check if we should be displaing the index.
  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    if ($sitemap->chunks > 1) {
      return 'index';
    }
    else {
      return 1;
    }
  }
  else {
    return (int) $_GET['page'];
  }
}

/**
 * Output a sitemap page.
 *
 * @see xmlsitemap_sitemap_load_by_context()
 * @see xmlsitemap_get_current_chunk()
 * @see xmlsitemap_sitemap_get_file()
 * @see xmlsitemap_output_file()
 */
function xmlsitemap_output_chunk() {
  $sitemap = xmlsitemap_sitemap_load_by_context();
  if (!$sitemap) {
    return MENU_NOT_FOUND;
  }

  $chunk = xmlsitemap_get_current_chunk($sitemap);
  $file = xmlsitemap_sitemap_get_file($sitemap, $chunk);

  // Provide debugging information if enabled.
  if (variable_get('xmlsitemap_developer_mode', 0) && isset($_GET['debug'])) {
    $output = array();
    $context = xmlsitemap_get_current_context();
    $output[] = "Current context: " . print_r($context, TRUE);
    $output[] = "Sitemap: " . print_r($sitemap, TRUE);
    $output[] = "Chunk: $chunk";
    $output[] = "Cache file location: $file";
    $output[] = "Cache file exists: " . (file_exists($file) ? 'Yes' : 'No');
    return implode('<br />', $output);
  }

  return xmlsitemap_output_file($file);
}

/**
 * Output the contents of a file to the browser and check caching headers.
 */
function xmlsitemap_output_file($file, array $headers = array()) {
  if (!file_exists($file) || !is_readable($file)) {
    return MENU_NOT_FOUND;
  }

  $mtime = filemtime($file);
  $last_modified = gmdate(DATE_RFC1123, $mtime);
  $etag = '"' . md5($last_modified) . '"';

  // See if the client has provided the required HTTP headers.
  $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : FALSE;
  $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : FALSE;
  if ($if_modified_since && $if_none_match && $if_none_match == $etag && $if_modified_since == $last_modified) {
    header('HTTP/1.1 304 Not Modified');
    // All 304 responses must send an etag if the 200 response for the same object contained an etag
    header('Etag: ' . $etag);
    exit;
  }

  $headers += array(
    'Content-type' => 'text/xml; charset=utf-8',
    //'Content-length' => filesize($file),
    'Last-modified' => $last_modified,
    'Etag' => $etag,
    'Expires' => gmdate(DATE_RFC1123, $mtime + variable_get('xmlsitemap_minimum_lifetime', 0)),
    'Cache-Control' => 'must-revalidate',
    'X-Robots-Tag' => 'noindex, follow',
  );

  // Transfer the file as output.
  xmlsitemap_file_transfer($file, $headers);
}

/**
 * Modified version of file_transfer() that invokes hook_exit()s afterwards.
 *
 * @see file_transfer()
 */
function xmlsitemap_file_transfer($uri, $headers) {
  if (ob_get_level()) {
    ob_end_clean();
  }

  foreach ($headers as $name => $value) {
    drupal_add_http_header($name, $value);
  }
  drupal_send_headers();

  // Attempt to increase time to transfer file.
  drupal_set_time_limit(240);

  $scheme = variable_get('file_default_scheme', 'public');
  // Transfer file in 16 KB chunks to save memory usage.
  if ($scheme && file_stream_wrapper_valid_scheme($scheme) && $fd = fopen($uri, 'rb')) {
    while (!feof($fd)) {
      print fread($fd, 1024*16);
    }
    fclose($fd);
  }
  else {
    drupal_not_found();
  }
  drupal_exit();
}

/**
 * Output an XML transformation file for the sitemap XML.
 */
function xmlsitemap_output_xsl() {
  // Read the XSL content from the file.
  $module_path = drupal_get_path('module', 'xmlsitemap');
  $xsl_content = file_get_contents($module_path . '/xsl/xmlsitemap.xsl');

  // Make sure the strings in the XSL content are translated properly.
  $replacements = array(
    'Sitemap file' => t('Sitemap file'),
    'Number of sitemaps in this index' => t('Number of sitemaps in this index'),
    'Click on the table headers to change sorting.' => t('Click on the table headers to change sorting.'),
    'Sitemap URL' => t('Sitemap URL'),
    'Last modification date' => t('Last modification date'),
    'Number of URLs in this sitemap' => t('Number of URLs in this sitemap'),
    'URL location' => t('URL location'),
    'Change frequency' => t('Change frequency'),
    'Priority' => t('Priority'),
    '[jquery]' => base_path() . 'misc/jquery.js',
    '[jquery-tablesort]' => base_path() . $module_path . '/xsl/jquery.tablesorter.min.js',
    '[xsl-js]' => base_path() . $module_path . '/xsl/xmlsitemap.xsl.js',
    '[xsl-css]' => base_path() . $module_path . '/xsl/xmlsitemap.xsl.css',
  );
  $xsl_content = strtr($xsl_content, $replacements);

  // Output the XSL content.
  drupal_add_http_header('Content-type', 'application/xml; charset=utf-8');
  drupal_add_http_header('X-Robots-Tag', 'noindex, follow');
  print $xsl_content;
}