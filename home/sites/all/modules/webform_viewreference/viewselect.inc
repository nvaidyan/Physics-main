<?php

/**
 * @file
 * Webform module multiple select component.
 */

/**
 * Implementation of _webform_defaults_component().
 */
function _webform_defaults_viewselect() {
  return array(
    'name' => '',
    'form_key' => NULL,
    'email' => 1,
    'mandatory' => 0,
    'pid' => 0,
    'weight' => 0,
    'value' => '',
    'extra' => array(
      'items' => '',
      'multiple' => NULL,
      'aslist' => NULL,
      'optrand' => 0,
      'other_option' => NULL,
      'other_text' => t('Other...'),
      'description' => '',
    ),
  );
}

/**
 * Implementation of _webform_theme_component().
 */
function _webform_theme_viewselect() {
  return array(
    'webform_viewselect_option_text' => array(
      'arguments' => array('rendered' => FALSE, 'row' => NULL, 'field' => NULL),
    ),
   );
}

/**
 * Implementation of _webform_edit_component().
 */
function _webform_edit_viewselect($component) {
  $form = array();

  $views = array('--' => '--');
  $all_views = views_get_all_views();
  foreach ($all_views as $view) {
    // Only 'node' views that have fields will work for our purpose.
    $allowed_types = array('node', 'term_data', 'users');
    if (in_array($view->base_table, $allowed_types) && !empty($view->display['default']->display_options['fields'])) {
      if ($view->type == 'Default') {
        $views[t('Default Views')][$view->name] = $view->name;
      }
      else {
        $views[t('Existing Views')][$view->name] = $view->name;
      }
    }
  }

  $form['extra']['advanced'] = array(
     '#type' => 'fieldset',
     '#title' => t('Advanced - Nodes that can be referenced (View)'),
     '#weight' => -100,
   );
   
  if (count($views) > 1) {
    $form['extra']['advanced']['advanced_view'] = array(
      '#type' => 'select',
      '#title' => t('View used to select the nodes'),
      '#options' => $views,
      '#required' => TRUE,
      '#default_value' => isset($component['extra']['advanced']['advanced_view']) ? $component['extra']['advanced']['advanced_view'] : '--',
      '#description' => t('<p>Choose the "Views module" view that selects the nodes that can be referenced.<br />Note:</p>') .
        t('<ul><li>Only views that have fields will work for this purpose.</li><li>Use the view\'s "sort criteria" section to determine the order in which candidate nodes will be displayed.</li></ul>'),
    );
    $form['extra']['advanced']['advanced_view_args'] = array(
      '#type' => 'textfield',
      '#title' => t('View arguments'),
      '#default_value' => isset($component['extra']['advanced']['advanced_view_args']) ? $component['extra']['advanced']['advanced_view_args'] : '',
      '#required' => FALSE,
      '#description' => t('Provide a comma separated list of arguments to pass to the view.'),
    );
    
   $form['extra']['advanced']['advanced_rendered'] = array(
      '#type' => 'radios',
      '#title' => t('Display text'),
      '#default_value' => isset($component['extra']['advanced']['advanced_rendered']) ? $component['extra']['advanced']['advanced_rendered'] : '0',
      '#required' => FALSE,
      '#options' => array('0' => t('Object title/name (node title)'), '1' => t('View fields')),
      '#description' => t('Choose which text that should be shown in the form to the end-user.'),
    );
  }
  
   $form['value'] = array(
    '#type' => 'textfield',
    '#title' => t('Default value'),
    '#default_value' => $component['value'],
    '#description' => t('The default value of the field. For multiple selects use commas to separate multiple defaults.') . theme('webform_token_help'),
    '#size' => 60,
    '#maxlength' => 256,
    '#weight' => 0,
  );
  
  $form['extra']['multiple'] = array(
    '#type' => 'checkbox',
    '#title' => t('Multiple'),
    '#return_value' => 'Y',
    '#default_value' => $component['extra']['multiple'],
    '#description' => t('Check this option if the user should be allowed to choose multiple values.'),
  );
  $form['extra']['aslist'] = array(
    '#type' => 'checkbox',
    '#title' => t('Listbox'),
    '#return_value' => 'Y',
    '#default_value' => $component['extra']['aslist'],
    '#description' => t('Check this option if you want the select component to be of listbox type instead of radiobuttons or checkboxes.'),
  );
  
  return $form;
}

/**
 * Element validation callback.
 */
/*
function _webform_edit_validate_viewselect($element, $form_state) {
  return TRUE;
}
*/

/**
 * Implementation of _webform_render_component().
 */
function _webform_render_viewselect($component, $value = NULL, $filter = TRUE) {
  $element = array(
    '#title'         => $component['name'],
    '#required'      => $component['mandatory'],
    '#weight'        => $component['weight'],
    '#description'   => $filter ? _webform_filter_descriptions($component['extra']['description']) : $component['extra']['description'],
  );

  // Convert the user-entered options list into an array.
  $default_value = $filter ? _webform_filter_values($component['value'], NULL, NULL, NULL, FALSE) : $component['value'];
  
  $args = $component['extra']['advanced']['advanced_view_args'];
  $rendered = $component['extra']['advanced']['advanced_rendered'];
  $options = _webform_viewselect_options($component['extra']['advanced']['advanced_view'], $args, FALSE, $rendered);
    
  if ($component['extra']['aslist'] === 'Y' && $component['extra']['multiple'] !== 'Y' && !($component['mandatory'] && !empty($component['value']))) {
    $options = array('' => t('select...')) + $options;
  }

  // Set the component options.
  $element['#options'] = $options;

  // Set the default value.
  if (isset($value)) {
    if ($component['extra']['multiple'] === 'Y') {
      // Set the value as an array.
      $element['#default_value'] = array();
      foreach ((array) $value as $key => $option_value) {
        $element['#default_value'][] = $option_value;
      }
    }
    else {
      // Set the value as a single string.
      $element['#default_value'] = '';
      foreach ((array) $value as $option_value) {
        $element['#default_value'] = $option_value;
      }
    }
  }
  elseif ($default_value != '') {
    // Convert default value to a list if necessary.
    if ($component['extra']['multiple'] === 'Y') {
      $varray = array_filter(explode(',', $default_value));
      foreach ($varray as $key => $v) {
        $element['#default_value'][] = $v;
      }
    }
    else {
      $element['#default_value'] = $default_value;
    }
  }
  elseif ($component['extra']['multiple'] === 'Y') {
    $element['#default_value'] = array();
  }

  if ($component['extra']['other_option'] === 'Y' && module_exists('select_or_other')) {
    // Set display as a select list:
    $element['#type'] = 'select_or_other';
    $element['#other'] = empty($component['extra']['other_text']) ? $component['extra']['other_text'] : t('Other...');
    $element['#other_unknown_defaults'] = 'other';
    $element['#other_delimiter'] = ', ';
    if ($component['extra']['multiple'] === 'Y') {
      $element['#multiple'] = TRUE;
      $element['#select_type'] = 'checkboxes';
    }
    else {
      $element['#multiple'] = FALSE;
      $element['#select_type'] = 'radios';
    }
    if ($component['extra']['aslist'] === 'Y') {
      $element['#select_type'] = 'select';
    }
  }
  elseif ($component['extra']['aslist'] === 'Y') {
    // Set display as a select list:
    $element['#type'] = 'select';
    if ($component['extra']['multiple'] === 'Y') {
      $element['#multiple'] = TRUE;
    }
  }
  else {
    if ($component['extra']['multiple'] === 'Y') {
      // Set display as a checkbox set.
      $element['#type'] = 'checkboxes';
      // Drupal 6 hack to properly render on multipage forms.
      $element['#process'] = array('webform_expand_checkboxes', 'webform_expand_select_ids');
    }
    else {
      // Set display as a radio set.
      $element['#type'] = 'radios';
      $element['#process'] = array('expand_radios', 'webform_expand_select_ids');
    }
  }

  $form_item['#prefix'] = '<div class="webform-component-' . $form_item['#type'] . '" id="webform-component-' . $component['form_key'] . '">';
  $form_item['#suffix'] = '</div>';

  return $element;
}

/**
 * Implementation of _webform_display_component().
 */
function _webform_display_viewselect($component, $value, $format = 'html') {
  $value = (array) $value;
  ksort($value);
  return array(
    '#title' => $component['name'],
    '#weight' => $component['weight'],
    '#theme' => 'webform_display_viewselect',
    '#theme_wrappers' => $format == 'html' ? array('webform_element') : array('webform_element_text'),
    '#post_render' => array('webform_element_wrapper'),
    '#component' => $component,
    '#format' => $format,
    '#value' => $value,
  );
}

/**
 * Implementation of _webform_submit_component().
 *
 * Convert FAPI 0/1 values into something saveable.
 */
function _webform_submit_viewselect($component, $value) {
  $rendered = $component['extra']['advanced']['advanced_rendered'];
  $options = _webform_viewselect_options($component['extra']['advanced']['advanced_view'], $component['extra']['advanced']['advanced_view_arguments'], TRUE, $rendered);
  if (is_array($value)) {
    foreach ($value as $key => $option_value) {
      // Handle options that are specified options.
      if ($option_value !== '' && isset($options[$key])) {
        // Checkboxes submit an *integer* value of 0 when not checked.
        if ($option_value === 0 && $options[$key] != '0' && $component['extra']['aslist'] !== 'Y' && $component['extra']['multiple'] === 'Y') {
          unset($value[$key]);
        }
        else {
          $value[$key] = $options[$key];
        }
      }
      else {
        unset($value[$key]);
      }
    }


    // Always save at least something, even if it's an empty single value.
    if (empty($value)) {
      $value[0] = '';
    }
  } else {
    // Single value
    $value = array($value => $options[$value]);
  }
  
  return $value;
}

/**
 * Module specific instance of hook_form_builder_types().
 */
function _webform_form_builder_types_viewselect() {
  $fields = array();

  $fields['select'] = array(
    'title' => t('Select list'),
    'properties' => array(
      'title',
      'description',
      'default_value',
      'required',
      'options',
      'multiple',
      'key',
    ),
    'default' => array(
      '#title' => t('New select list'),
      '#type' => 'select',
      '#options' => array('one' => 'one', 'two' => 'two', 'three' => 'three'),
      '#default_value' => 'one',
      '#webform' => array('type' => 'select'),
    ),
  );

  $fields['checkboxes'] = array(
    'title' => t('Checkboxes'),
    'properties' => array(
      'title',
      'description',
      'default_value',
      'required',
      'options',
      'multiple',
      'key',
    ),
    'default' => array(
      '#title' => t('New checkboxes'),
      '#type' => 'checkboxes',
      '#options' => array('one' => 'one', 'two' => 'two', 'three' => 'three'),
      '#default_value' => array('one', 'two'),
      '#multiple' => TRUE,
      '#webform' => array('type' => 'select'),
    ),
  );

  $fields['radios'] = array(
    'title' => t('Radios'),
    'properties' => array(
      'title',
      'description',
      'default_value',
      'required',
      'options',
      'key',
    ),
    'default' => array(
      '#title' => t('New radios'),
      '#type' => 'radios',
      '#options' => array('one' => 'one', 'two' => 'two', 'three' => 'three'),
      '#default_value' => 'one',
      '#webform' => array('type' => 'select'),
    ),
  );

  return $fields;
}

/**
 * Module specific instance of hook_form_builder_load().
 */
function _webform_form_builder_load_viewselect($form_element) {
  if (in_array($form_element['#type'], array('radios', 'checkboxes', 'select'))) {
    $form_element['#webform']['type'] = 'select';
    if ($form_element['#type'] == 'checkboxes') {
      $form_element['#multiple'] = TRUE;
    }
    return $form_element;
  }
}

/**
 * Implementation of _webform_form_builder_save_component().
 */
function _webform_form_builder_save_viewselect($component, $form_element) {
  $options = '';

  foreach ($form_element['#options'] as $key => $option_value) {
    $options .= $key .'|'. $option_value ."\n";
  }

  $component['extra']['items'] = $options;
  $component['extra']['multiple'] = empty($form_element['#multiple']) ? 'N' : 'Y';
  $component['extra']['aslist'] = $form_element['#type'] == 'select' ? 'Y' : 'N';
  $component['extra']['description'] = isset($form_element['#description']) ? $form_element['#description'] : '';
  return $component;
}


/**
 * Implementation of _webform_analysis_component().
 */
function _webform_analysis_viewselect($component, $sids = array(), $single = FALSE) {
  // TODO: FIXA
  $options = _webform_viewselect_options($component['extra']['items'], TRUE);
  $show_other_results = $single;

  $sid_placeholders = count($sids) ? array_fill(0, count($sids), "'%s'") : array();
  $sid_filter = count($sids) ? " AND sid IN (".implode(",", $sid_placeholders).")" : "";

  $not = $show_other_results ? 'NOT ' : '';
  $placeholders = count($options) ? array_fill(0, count($options), "'%s'") : array();
  $query = 'SELECT data, count(data) as datacount ' .
    ' FROM {webform_submitted_data} ' .
    ' WHERE nid = %d ' .
    ' AND cid = %d ' .
    " AND data != ''" . $sid_filter .
    ($placeholders ? ' AND data ' . $not . 'IN (' . implode(',', $placeholders) . ')' : '') .
    ' GROUP BY data ';

  $count_query = 'SELECT count(*) as datacount ' .
    ' FROM {webform_submitted_data} ' .
    ' WHERE nid = %d ' .
    ' AND cid = %d ' .
    " AND data != ''" . $sid_filter;

  $result = db_query($query, array_merge(array($component['nid'], $component['cid']), $sids, array_keys($options)));
  $rows = array();
  $normal_count = 0;
  while ($data = db_fetch_array($result)) {
    $display_option = $single ? $data['data'] : $options[$data['data']];
    $rows[$data['data']] = array(_webform_filter_xss($display_option), $data['datacount']);
    $normal_count += $data['datacount'];
  }

  if (!$show_other_results) {
    // Order the results according to the normal options array.
    $ordered_rows = array();
    foreach (array_intersect_key($options, $rows) as $key => $label) {
      $ordered_rows[] = $rows[$key];
    }
  
    // Add a row for any unknown or user-entered values.
    if ($component['extra']['other_option'] === 'Y') {
      $full_count = db_result(db_query($count_query, array_merge(array($component['nid'], $component['cid']), $sids)));
      $other_count = $full_count - $normal_count;
      $display_option = empty($component['extra']['other_text']) ? $component['extra']['other_text'] : t('Other...');
      $other_text = $other_count ? $other_count . ' (' . l(t('view'), 'node/' . $component['nid'] . '/webform-results/analysis/' . $component['cid']) . ')' : $other_count;
      $ordered_rows[] = array(check_plain($display_option), $other_text);
    }

    $rows = $ordered_rows;
  }

  return $rows;
}

/**
 * Implementation of _webform_table_component().
 */
function _webform_table_viewselect($component, $value) {
  $output = '';
  // Set the value as a single string.
  if (is_array($value)) {
    foreach ($value as $option_value) {
      if ($option_value !== '') {
        $output .= _webform_filter_xss($option_value) .'<br />';
      }
    }
  }
  else {
    $output .= empty($value[0]) ? '' : _webform_filter_xss($value[0]);
  }
  return $output;
}

/**
 * Implementation of _webform_csv_headers_component().
 */
function _webform_csv_headers_viewselect($component, $export_options) {
  $headers = array(
    0 => array(),
    1 => array(),
    2 => array(),
  );

  if ($component['extra']['multiple'] && $export_options['select_format'] == 'separate') {
    $headers[0][] = '';
    $headers[1][] = $component['name'];
    // TODO: FIXA
    $items = _webform_viewselect_options($component['extra']['items'], TRUE);
    if ($component['extra']['other_option'] === 'Y') {
      $other_label = !empty($component['extra']['other_text']) ? $component['extra']['other_text'] : t('Other...');
      $items[$other_label] = $other_label;
    }
    $count = 0;
    foreach ($items as $key => $item) {
      // Empty column per sub-field in main header.
      if ($count != 0) {
        $headers[0][] = '';
        $headers[1][] = '';
      }
      $headers[2][] = $key;
      $count++;
    }
  }
  else {
    $headers[0][] = '';
    $headers[1][] = '';
    $headers[2][] = $component['name'];
  }
  return $headers;
}

/**
 * Implementation of _webform_csv_data_component().
 */
function _webform_csv_data_viewselect($component, $export_options, $value) {
  $return = array();

  if ($component['extra']['multiple']) {
    foreach ($value as $key => $item) {
      $return[] = ($export_options['select_format'] == 'separate') ? 'X' : $item;
      unset($value[$index]);
    }
  }
  else {
    $return = $value[0];
  }

  if ($component['extra']['multiple'] && $export_options['select_format'] == 'compact') {
    $return = implode(',', (array) $return);
  }

  return $return;
}

/**
 * Utility function to split user-entered values from new-line seperated
 * text into an array of options.
 *
 * @param $view
 *   Text to be converted into a select option array.
 * @param $flat
 *   Optional. If specified, return the option array and exclude any optgroups.
 * @param $filter
 *   Optional. Whether or not to filter returned values.
 */
function _webform_viewselect_options($view, $args = '', $strict = TRUE, $rendered = FALSE) {
  $options = array();

  // Prepare field array - Reusing CCK nodreference function _nodereference_potential_references()
  $field['advanced_view'] = $view;
  $field['advanced_view_args'] = $args;
  // Field name used for cid in _nodereference_potential_references
  $field['field_name'] = md5($view.$args); 
  
  $data = _webform_nodereference_potential_references($field);
  
  foreach($data as $index => $row) { 
    $options[$index] = theme('webform_viewselect_option_text', $rendered, $row, $field); 
  }

  return $options;
}

function theme_webform_viewselect_option_text($rendered, $row, $field) {
  if($rendered) {
    return html_entity_decode(_webform_filter_xss($row['rendered'])); 
  } else {
    return html_entity_decode(_webform_filter_xss($row['title'])); 
  }  
}