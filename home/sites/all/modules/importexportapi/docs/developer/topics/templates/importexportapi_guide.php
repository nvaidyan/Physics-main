<?php

/**
 * @file
 * Template script used to generate the importexportapi_guide.html
 * document. The code in this file is designed to be run through the
 * PHPTemplate system with the codefilter module in use. If the code in this
 * file is rendered without the help of codefilter, the PHP code examples will
 * not display properly.
 */

require_once('./pages/reference_entities.php');

?>
<p>The Drupal Import / Export API is a highly flexible framework for getting data into and out of Drupal. In actual fact, it's a framework for getting data out of any one source, and into any other source. However, the most common uses for the API involve having Drupal as one of these sources. So, to make life simpler, you should think of the API in terms of its more limited definition, unless (or until) you reach a point where you need to go beyond this.</p>

<p><strong>Contents:</strong></p>

<ul>
  <li><a href="#overview">Overview</a></li>
  <li><a href="#definitions">Writing data definitions</a><ul>
    <li><a href="#definition_structure">Definition structure</a></li>
    <li><a href="#references">References</a></li>
    <li><a href="#keys">Keys</a></li>
    <li><a href="#returning_definitions">Returning definitions</a></li>
    <li><a href="#altering_definitions">Altering definitions</a></li>
    <li><a href="#defining_db_tables">Defining database tables</a></li>
  </ul></li>
  <li><a href="#field_types">Defining field types</a></li>
  <li><a href="#writing_get_put_engines">Writing 'get' and 'put' engines</a><ul>
    <li><a href="#defining_engines">Defining engines</a></li>
    <li><a href="#implementing_build_callbacks">Implementing build callbacks</a></li>
    <li><a href="#writing_engines">Writing engines</a></li>
  </ul></li>
  <li><a href="#utilising_the_api">Utilising the API</a></li>
</ul>

<p>This document provides an introduction on how you can build upon and utilise the API. It is not a comprehensive guide, but it should certainly help you to get a grasp of the basics, and hopefully it will whet your appetite for more! If you're looking for detailed reference documentation on the API, please consult <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html">the reference guide</a>.</p>

<h2>Overview</h2>

<p>There are three main ways in which you can build upon the API. The first and simplest (and most common) way is by writing data definitions, which are structured arrays that represent entities within Drupal. Each entity consists of fields, which are of particular types, and which have certain properties. The API provides a rich repertoire of field types and properties, which should be enough to allow almost any entity to be accurately represented in code.</p>

<p>The second way is by defining new field types. Custom field types can have their own default values for properties, and they can have special processing callbacks, that allow you to programmatically control their behaviour in whatever way you need to. For example, you can define callbacks that transform the data that your field type stores, from one format to another, whenever that data gets imported or exported between various sources.</p>

<p>The third and most powerful (and most difficult!) way is by defining new engines for the API. Engines are the heart and soul of the Import / Export API: without them, the API would be useless. Each engine is responsible for either getting a set of data from a particular source, or for putting data into a particular source. As such, there are two types of engines: 'get' engines, and 'put' engines. Each engine can define its own properties, which can then be used when writing data definitions. Engines do their job with the help of data definitions: an engine is not expected to be able to get or to put an entity, if there is no data definition available for that entity.</p>

<p>As well as building upon the API, you can also utilise it. The most common way to do this is by developing an import / export <abbr title="Graphical User Interface">GUI</abbr>; but really, what you can do to utilise the API is limited only by your imagination. The API can be used in such versatile applications as automated backup, database merging, multi-site content sharing, and data migration.</p>

<h2><a name="definitions" id="definitions"></a>Writing data definitions</h2>

<p>Data definitions are defined in a structured array format, which is very similar to the format used to define forms in the <a href="http://api.drupal.org/api/head/file/developer/topics/forms_api.html">Drupal Forms API</a>. Each element of the array is a <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#fields">field</a>, which in turn contains more elements, which are all either <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#properties">properties</a> of the field, or child fields.</p>

<p>Anyway, enough talk: for now, let's jump right into an example of how to define a field:</p>

<?php
$usage = "
<?php
\$def['bar']['foo_id'] = array(
  '#type' => 'int',
  '#title' => t('Foo ID'),
  '#reference_entity' => 'foo'
);
?>
";
print $usage;
?>

<p>A few things to note:</p>

<ol>
<li>The key of the array element determines the field's name. If the field is several elements deep, then the right-most key is the field's name (which in this case is <code>'foo_id'</code>), and the other keys are the names of the field's parents (just one parent in this case: <code>'bar'</code>).</li>
<li>The field's properties are defined as sub-elements, and are denoted by having a <code>#</code> character at the start of their key.</li>
<li>The field type can be easily determined by looking at the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#type">#type</a> property. In this case, the type is <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#int">int</a>. If no field type is defined, then the default type <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#string">string</a> is assumed.</li>
<li>The field type reflects the low-level format in which the field's value is stored in its source. It does NOT reflect either the semantic meaning of this field, or the way in which the field's value should be presented in any given context. As such, most of the field types are similar to the variable types available in general-purpose programming languages, such as PHP.</li>
<li>We can work out what data the field is storing by looking at the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#title">#title</a> property, which gives us a meaningful, human-readable description of the field. The title is the only property that is universally required for all field types.</li>
<li>The <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#reference_entity">#reference_entity</a> property indicates that fields can be references to other fields. Referencing is the mechanism for defining relationships between fields (and hence between entities) in the API, and as such, it is a crucial concept to understand when writing data definitions.</li>
<li>Nowhere in this field definition is the field's value defined. Field values are not set in the data definition, but instead are acquired during a 'get' operation, and are then used in a 'put' operation. We could have used the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#default_value">#default_value</a> property to define the field's default value, but instead, we chose to let the API use the default value for this property, which is provided by the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#int">int</a> field type.</li>
</ol>

<p>Here's a real-life example of a (relatively) complete data definition for an entity in Drupal:</p>

<?php
$usage = "
<?php
/**
 * Implementation of hook_def().
 */

function user_def() {
  \$defs = array();
  // snip ...

  \$def = array(
    '#type' => 'entity',
    '#title' => t('User'),
    '#db_default_table' => 'users',
    '#xml_plural' => 'users',
    '#csv_plural' => 'users'
  );

  \$def['uid'] = array(
    '#type' => 'int',
    '#title' => t('User ID'),
    '#key' => TRUE,
    '#db_uses_sequences' => TRUE
  );

  \$def['name'] = array(
    '#title' => t('Username'),
    '#alt_key_for' => 'uid'
  );

  \$def['pass'] = array(
    '#title' => t('Password')
  );

  \$def['mail'] = array(
    '#title' => t('E-mail address'),
    '#alt_key_for' => 'uid'
  );

  \$def['theme'] = array(
    '#title' => t('Theme configuration')
  );

  \$def['created'] = array(
    '#type' => 'datetime',
    '#title' => t('Member since')
  );

  \$def['access'] = array(
    '#type' => 'datetime',
    '#title' => t('Last access')
  );

  \$def['login'] = array(
    '#type' => 'datetime',
    '#title' => t('Last login')
  );

  \$def['status'] = array(
    '#type' => 'int',
    '#title' => t('Status')
  );

  \$def['timezone'] = array(
    '#type' => 'int',
    '#title' => t('Time zone'),
    '#default_value' => NULL
  );

  \$def['picture'] = array(
    '#type' => 'file',
    '#title' => t('Picture')
  );

  \$def['roles'] = array(
    '#type' => 'array',
    '#title' => t('Roles'),
    '#db_default_table' => 'users_roles',
    '#xml_plural' => 'roles',
    '#csv_plural' => 'users-roles',
    '#xml_mapping' => 'role'
  );

  \$def['roles']['uid'] = array(
    '#type' => 'int',
    '#title' => t('User ID'),
    '#reference_entity' => 'user',
    '#key_component' => TRUE
  );

  \$def['roles']['rid'] = array(
    '#type' => 'int',
    '#title' => t('Role ID'),
    '#reference_entity' => 'role',
    '#key_component' => TRUE
  );

  // snip ...

  \$defs['user'] = \$def;

  return \$defs;
}
?>
";
print $usage;
?>

<p>The most important thing to observe in this example, is that for every relevant field in the database, an entity's data definition should have a corresponding field defined. As the user entity example illustrates, these fields may not necessarily all be in the same database table.</p>

<p>In the example above, we seem to be making an implicit assumption that the central source of data for this entity is the database, and that the entire entity definition should be closely modelled on the entity's structure in the database. Why are we doing this? Isn't the API meant to be about getting data in and out of any source, not just the database? The reason for the database-centric modelling is the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#source">#source</a> property, which defines the 'primary source' of data for this entity. We haven't specified #source in this example, so the default value, 'db' (database) is used.</p>

<p>Another important thing to note: all engine-specific properties are prefixed by the name of their engine, and an underscore (e.g. <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference_db.html#db_default_table">#db_default_table</a>, <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference_csv.html#csv_plural">#csv_plural</a>). In general, engine-specific properties are inherited from non-engine-specific properties in some shape or form (consult the reference guides for details).</p>

<h3><a name="definition_structure" id="definition_structure"></a>Definition structure</h3>

<p>An entity definition has three main levels. The first level is the definition of the entity itself. An <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#entity">entity</a> is a field type just like any other, and it must be specified as such, even though all top-level fields must be entities. The second level is the 'base fields' of the entity. These are the fields that have only one value per entity (i.e. they have a 1-1 mapping with the entity). The third level (which not all entities have) is the 'multi fields' of the entity. These fields may have multiple values per entity (i.e. they have a 1-M mapping with the entity). Each set of 'multi fields' is stored within a field of type <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#array">array</a>. Array fields may contain other array fields, which can be nested as deeply as you wish. Each set of 'multi fields' has a 1-M mapping with the array field that is its direct parent. However, arrays cannot represent fields that have a M-N (many-to-many) mapping with each other: for this, you will need to use separate entities with references.</p>

<p>In many ways, an array field is almost a separate entity in itself, rather than a child of its parent entity. Array fields are generally treated as separate entities by the various 'get' and 'put' engines: for example, the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference_db.html">db engines</a> always generate separate queries for array fields. However, array fields must be linked back to their parents, by having at least one field that references a field in the parent array or entity. For example, in the 'roles' array (in the example above), the 'uid' field references the 'uid' field of the parent entity (i.e. 'user').</p>

<h3><a name="references" id="references"></a>References</h3>

<p>This brings us to the important question: what are references, and how are they defined? A reference is simply a way of saying: <em>the value in field 'x' corresponds to one of the values in field 'y'</em>. Fields 'x' and 'y' can be anywhere: they can be sibling fields; one can be the 'parent' of the other; they can be at different levels of the same entity structure; or they can be in completely different entities. For those of you that are familiar with database theory, a reference field is the rough equivalent of a 'foreign key' field in a database. References are used to link two fields together, and consequently, they can also link the levels of an entity, and they can serve as links between two separate entities.</p>

<p>References are defined by using the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#reference_entity">#reference_entity</a> property, on the field that is a reference to another field. The #reference_entity property determines the entity in which the field being referenced is located. The optional <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#reference_field">#reference_field</a> property can also be used, to specify the exact field that is being referenced; if #reference_field is not defined, then the field is assumed to be a top-level field in #reference_entity, with the same name as the field that's doing the referencing. Therefore, if the field being referenced is within an array field, you will always need to specify #reference_field in order to reference it.</p>

<h3><a name="keys" id="keys"></a>Keys</h3>

<p>Related to the concept of references is that of <em>keys</em>. Every entity and array must contain at least one key field. The combination of all key fields in an entity or array forms the unique identifier for that entity or array. There are two ways to specify a key field. The first way is to set the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#key">#key</a> property of a field to TRUE: #key fields are very special, in that they are integer fields whose value is database-generated, and in that they are unique in and of themselves (i.e. every value of a #key field is unique, not just every combination of #key and other key values). The second way is to set the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#key_component">#key_component</a> property of a field to TRUE: #key_component fields can be integers or strings, and they do not have to be unique in and of themselves; they just have to be unique in combination with all other #key and #key_component fields.</p>

<p>References and keys are closely related concepts, because every reference field must be a reference to a key field. Reference fields can also be a reference to a third type of field, which is a pseudo-key field: that is, an <em>alternate key field</em>. Alternate key fields are another special type of field, in that all of their values must be unique, and in that each value corresponds with the value of a sibling #key field. As such, alternate key fields can be used as a reliable alternative to referencing the #key field itself. Alternate key fields are specified by using the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#alt_key_for">#alt_key_for</a> attribute, which defines the #key field for which it is an alternate key field. Alternate keys also have the special (and somewhat complex) behaviour of having copies of themselves generated, as siblings of any fields that reference the #key field specified by their #alt_key_for property.</p>

<h3><a name="returning_definitions" id="returning_definitions"></a>Returning definitions</h3>

<p>As the user entity example (above) shows, definitions are defined as a structured array (with each top-level element being an entity), and are returned by implementing <code>hook_def()</code> within a module. The general convention is to build up the definition for each entity within an array called <code>$def</code>, and to then copy <code>$def</code> into the <code>$defs</code> array as a child element. This allows you to easily define as many entities as you want within one <code>hook_def()</code> implementation. The <code>$defs</code> array is then returned, as follows:</p>

<?php
$usage = "
<?php
function foo_def() {
  \$defs = array();

  \$def['bar']['foo_id'] = array(
    '#type' => 'int',
    '#title' => t('Foo ID'),
    '#reference_entity' => 'foo'
  );

  \$defs['foo'] = \$def;

  return \$defs;
}
?>
";
print $usage;
?>

<h3><a name="altering_definitions" id="altering_definitions"></a>Altering definitions</h3>

<p>You can also alter the definitions that other modules have defined, using <code>hook_def_alter()</code>. Using <code>hook_def_alter()</code> is recommended for entities that are clearly 'tied' to one particular module, but that have some fields for which other modules are responsible. You should also use <code>hook_def_alter()</code> for fields that are optionally and/or dynamically appended to a definition, based on run-time conditions. <code>hook_def_alter()</code> passes the array of all definitions as an argument (by reference), which means that modules don't have to return anything - they just alter the array directly, and then let the function finish:</p>

<?php
$usage = "
<?php
function bar_def_alter(&\$def) {
  if (isset(\$def['foo'])) {
    \$def['foo']['bar_field'] = array(
      '#type' => 'int',
      '#title' => t('Bar field')
    );
  }
}
?>
";
print $usage;
?>

<p><strong>Note:</strong> <code>hook_def_alter()</code> shares the weaknesses of other, similar Drupal hooks - such as <code>hook_form_alter()</code> - in that it cannot easily allow altered definitions to be altered. In general, whenever you're implementing <code>hook_def_alter()</code>, you should assume that the only definitions available to you are those defined through <code>hook_def()</code>. It is possible to arrange for <code>hook_def_alter()</code> functions to execute in a certain order, by setting module weights in a site's 'system' database table, but this can get messy and is not encouraged.</p>

<p><strong>Tip:</strong> if you need to alter the definition of the base node entity (which is inherited by all node type entities), then you should implement <code>hook_node_def_alter()</code> instead of <code>hook_def_alter()</code>, because the former lets you modify the base node definition before it gets copied over to the definitions for each node type.</p>

<h3><a name="defining_db_tables" id="defining_db_tables"></a>Defining database tables</h3>

<p>One other little thing that you'll need to do when writing data definitions: whenever you're defining entities whose source is the 'db' (i.e. virtually all the time), you need to tell the 'db' engines about your database tables. For every database table that you use, the engines need to know what prefix to use when generating SQL queries. This is done by implementing <code>hook_db_def_tables()</code>, as follows:</p>

<?php
$usage = "
<?php
function foo_db_def_tables() {
  \$tables = array();

  \$tables['foobar'] = 'fb';

  return \$tables;
}
?>
";
print $usage;
?>

<p>You may be wondering why the prefixes need to be manually defined, when the engines could easily just use the full table names as prefixes, or automatically generate unique prefixes based on the table names. The reason is simple: the API wants to generate SQL that is as clean as possible, and it's demanding that you help it to achieve this (by requiring defined table prefixes). SQL with concise yet meaningful table prefixes is much easier to read, and much easier to debug (should you or anyone else ever need to do this). Always check that the prefixes you define aren't already taken, otherwise you may encounter some problems during import or export.</p>

<h2><a name="field_types" id="field_types"></a>Defining field types</h2>

<p>Defining field types is not the exclusive right of the core API: any module can do it. Field types are defined through <code>hook_def_field_types()</code>, like this:</p>

<?php
$usage = "
<?php
function foo_def_field_types(\$op) {
  \$type = array();

  switch (\$op) {
    case 'placeholders':
      \$type['foofield'] = \"'%s'\";
      break;
    case 'defaults':
      \$type['foofield'] = array(
        '#required' => FALSE,
        '#default_value' => '',
        '#process' => array('foo_process_foofield' => array())
      );
      break;
  }

  return \$type;
}
?>
";
print $usage;
?>

<p>When implementing <code>hook_def_field_types()</code>, your code needs to cater to two different values for <code>$op</code>. The first one, 'placeholders', is just for defining SQL placeholders to be used when querying your field from the database (for use by the 'db' engines), and isn't that big of a deal. The second one, 'defaults', is where you can define any default values that you wish, for various properties that fields of this type can have. For example, field types that are stored as a string in the database generally set the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#default_value">#default_value</a> property to a default of '' (empty string).</p>

<p>The 'defaults' area is also where you can define the default <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#process">#process</a> callback(s) for your field type. Process callbacks can be used for virtually anything, but they're generally used for performing a simple transformation on your field, during a particular type of 'get' or 'put' operation. For example, the 'datetime' field type uses a callback in order to transform timestamps into string-representations of dates (and vice versa). For every #process callback that you specify, you must have a function with that name specified in your module's code. Also, unlike other default values for properties, if individual instances of your field type define their own #process callbacks, then those callbacks are <em>appended</em> to the default callbacks, rather than overwriting them. So don't worry - default #process callbacks <em>always</em> get executed when they're meant to!</p>

<h2><a name="writing_get_put_engines" id="writing_get_put_engines"></a>Writing 'get' and 'put' engines</h2>

<p>For those of you that really want to push the limits of what the Import / Export API can do, there is also the possibility of writing new 'get' and 'put' engines. A 'get' engine is a function (and usually a set of supporting functions) that can retrieve a set of data from a source (e.g. the database, an XML file), and that can place all available values from that source into the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#value">#value</a> property of the fields in a data definition array. Conversely, a 'put' engine is a function (and co) that can be handed a data definition, with the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#value">#value</a> property of the definition's fields pre-populated (usually by a 'get' engine), and that can put those values into a (usually different) source.</p>

<p>'Get' and 'put' engines usually come in pairs which complement each other, and which are related to the same source or format of data. Many of the paired engines even share some of their code (this sharing is handled internally by the engines - the API does not assist with it). However, there are no hard and fast rules about this: paired engines do not need to share code (although it's probably not a bad idea); and engines don't always have to come in pairs (although if you want to get data from a particular source, it's likely that you're going to want to put data into it as well).</p>

<h3><a name="defining_engines" id="defining_engines"></a>Defining engines</h3>

<p>The first step in writing 'get' and 'put' engines is to tell the API about them. This is done by implementing <code>hook_engines_get_put()</code>. This hook tells the API everything it needs to know about your module's engines (things such as their horsepower, their fuel consumption rate, number of cylinders, etc :P). The hook is implemented as follows:</p>

<?php
$usage = "
<?php
function foo_engines_get_put() {
  return array(
    'foo' => array(
      'title' => t('Foo'),
      'get' => array(
        'callback' => 'foo_get',
        'build' => array('foo_build' => array('get')),
        'build_alt_key' => array('foo_build_alt_key' => array('get'))
      ),
      'put' => array(
        'callback' => 'foo_put',
        'build' => array('foo_build' => array('put')),
        'build_alt_key' => array('foo_build_alt_key' => array('put'))
      )
    )
  );
}
?>
";
print $usage;
?>

<p>As you can see, implementations of this hook need to return a structured array about the module's engines. The array is grouped first by engine name ('foo' in this case), and then by engine type (either 'get' or 'put'). So in this case, two engines are being defined: 'foo' 'get', and 'foo' 'put'. A title is also defined, and this title applies to both engines (if there are two) in each grouping.</p>

<p>Within each engine, up to three sets of callback functions are defined. Of these, only the first callback - which is simply called 'callback' - is required. The 'callback' defines the callback function that will result in this engine actually being invoked, which in this case is the <code>foo_get()</code> function, and the <code>foo_put()</code> function. The optional 'build' array specifies one or more callback functions, and their arguments, that will be invoked when building data definitions, in preparation for use with this engine. The (also optional) 'build_alt_key' array is for callback functions relating to the building of generated alternate key fields, which are already built before being copied and generated, but which may require additional building after as well.</p>

<h3><a name="implementing_build_callbacks" id="implementing_build_callbacks"></a>Implementing build callbacks</h3>

<p>Build callbacks allow your engines to alter all available data definitions, which they will probably need to do in order to make the definitions usable by the main engine callback. For example, a callback may need to set default values for engine-specific field properties, or it may even need to transform the definition by altering existing, non-engine-specific properties (be careful when doing this - the definitions still need to be usable by other engines):</p>

<?php
$usage = "
<?php
function foo_build(\$field, \$op) {
  \$values = array();
  \$field_array = array();
  \$op_orig = \$op;
  \$op = '#'. \$op;

  if (\$field['#type'] == 'entity') {
    \$field_array[] = \$field['#id'];
  }
  else {
    \$field_array = \$field['#parents'];
    \$field_array[] = \$field['#id'];
  }

  if ((\$field['#type'] != 'entity' && \$field['#type'] != 'array') || isset(\$field['#foo_mapping'])) {
    if (!isset(\$field[\$op]['#foo_mapping'])) {
      \$values[\$op]['#foo_mapping'] = isset(\$field['#foo_mapping'])
        ? \$field['#foo_mapping']
        : str_replace('_', '-', \$field['#id']);
    }
  }

  if (!empty(\$values)) {
    importexportapi_set_def(\$field_array, \$values);
  }

  foreach (element_children(\$field) as \$child) {
    foo_build(\$field[\$child], \$op_orig);
  }
}
?>
";
print $usage;
?>

<p>The build callback is passed as its default parameter the <code>$field</code> variable - which is the top-level entity for each available entity definition. It is also passed any additional parameters that were specified in <code>hook_engines_get_put()</code> - which in this case is only the <code>$op</code> parameter. Since the build callback is only invoked for each top-level entity field, it is the responsibility of the callback function to traverse down through child fields if necessary: this callback function does this through the use of recursion, as do most others.</p>

<p>There is also no way to return or to pass back the modifications that were made to the data definition by the callback. Instead, the callback can save its modifications back to the master definition store, by using the <code>importexportapi_set_def()</code> function. In general, all modifications should be made within either the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#get">#get</a> or the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#put">#put</a> property of each field, as this allows engine pairs to share properties, but to also work with separate instances of these properties, and to allow module developers to specify separate values for each instance.</p>

<p>The alternate key building callback works very similarly to the main build callback, except that it is only invoked for generated alternate key fields (which will never have any child fields), and as such, there is no need to recursively traverse down through child fields. You should remember that once the alternate key callback is invoked, the generated field will already have been passed through the main build callback(s) for this engine, and so only additional, generated-field-specific building should be done here. Not all engines will need to implement this callback.</p>

<h3><a name="writing_engines" id="writing_engines"></a>Writing engines</h3>

<p>To actually write your engine, you need to implement the function specified by the 'callback' element in <code>hook_engines_get_put()</code>. Here's some pseudo-code for a 'get' engine callback:</p>

<?php
$usage = "
<?php
function foo_get(\$def, \$variables) {
  \$data = array();

  /* engine magic goes here */

  return \$data;
}
?>
";
print $usage;
?>

<p>As this example shows, a 'get' engine callback needs to specify two parameters: <code>$def</code>, which is the definition for the entity that the callback is to get data for; and <code>$variables</code>, which is an array that can contain whatever additional information this engine needs in order to do its job (e.g. raw input data, some sort of operation ID, etc). A 'get' engine also needs to return <code>$data</code>, which is an array containing a copy of <code>$def</code> for each set of entity data found, and which has each copy of <code>$def</code> populated with the acquired values.</p>

<p>Similarly, here's some pseudo-code for a 'put' engine callback:</p>

<?php
$usage = "
<?php
function foo_put(\$data, \$variables) {
  \$export = '';

  /* engine magic goes here */

  return \$export;
}
?>
";
print $usage;
?>

<p>A 'put' engine callback also needs to specify two parameters: <code>$data</code>, which is basically the return value of a 'get' engine callback; and <code>$variables</code>, which has the same purpose here as it does for 'get' engine callbacks. Unlike 'get' engines, there is no requirement as to what 'put' engine callbacks should return: in fact, they don't have to return anything at all! Some callbacks (such as this one) directly return the data that they have 'put', in the format that they have 'put' it in. It's then up to developers of other modules (such as UI modules) to figure out what to do with this data. Other callbacks simply return a status message indicating the results of the operation, or a pointer to the resource (such as a file) where the 'put' data can be found. Don't worry about what (if anything) your engine's 'put' callback returns: it doesn't have to be consistent with what other engines return. And dealing with the return value is somebody else's problem :P (unless that somebody is you!).</p>

<p>Everything else - all the magic that happens in between the start and the end of a 'get' or 'put' engine callback function - is completely up to you, as the engine developer. There isn't really much more to be said about writing engines, except that you should use your ingenuity and your imagination when writing them, and that you should refer to the core engines (such as the 'db' engines) for help by way of example. It is also highly recommended that you implement support for some sort of mapping system in your engine, in order to make it as flexible and as customisable as possible. Refer to the core engines for examples of how to implement a mapping system.</p>

<h2><a name="utilising_the_api" id="utilising_the_api"></a>Utilising the API</h2>

<p>You've already been told that you can <em>do</em> virtually anything with the API, and you've already been given some examples (in the intro) of what constitutes 'anything' in this context. So instead of showing you directly how you can utilise the API, this section is simply concerned with introducing you to the more important functions in the API.</p>

<p>As a module developer utilising the API, the two most important functions that you need to know about are <code>importexportapi_get_data()</code>, and <code>importexportapi_put_data()</code>. If these functions weren't so damn quick and easy to use, you'd even have time to become best friends with them. Here's an example of how to call these two functions (and yes, they're usually called together):</p>

<?php
$usage = "
<?php
function foo_do_something_cool(\$info) {
  \$entity_list = \$info['entity_list'];
  \$engine = \$info['engine'];

  \$data = importexportapi_get_data(\$entity_list, \$engine, array('foo'));
  \$ret = importexportapi_put_data(\$data, NULL, array('foo'));
  
  return \$ret;
}
?>
";
print $usage;
?>

<p>The <code>importexportapi_get_data()</code> function only has one required parameter: the first one, which is an array listing all the entities that the API is to 'get' value-populated sets of definitions for. The 'engine' parameter specifies the 'get' engine to be used in performing the operation: if no engine is specified, then the engine specified by the <a href="<?php print IMPORTEXPORTDOCS_BASE_URL; ?>docs/developer/topics/importexportapi_reference.html#source">#source</a> property of the first available entity in the list is used. The 'variables' parameter specifies any additional info to be passed to the engine; and the 'def' parameter (not used in this example) allows you to pass in your own definition array, rather than simply using the master definition store. This function always returns an array of value-populated sets of entity definitions, as returned by all 'get' engines.</p>

<p>The signature of the <code>importexportapi_put_data()</code> function is very similar. It also has only one required parameter: the first one, which is the 'data' value (as returned by <code>importexportapi_get_data()</code>). The 'engine' parameter specifies the 'put' engine to be used in performing the operation (same rules as with <code>importexportapi_get_data()</code> if no engine is specified); and the 'variables' parameter is the same as for <code>importexportapi_get_data()</code>. As with the 'put' engines themselves, the return value of this function varies, depending on the engine being used.</p>

<p>Technically speaking, these two functions are all you need in order to perform an import or an export in whatever shape or form you please. However, there are a few other functions that will make your life easier when utilising the API:</p>

<dl>
<dt><code>importexportapi_get_def()</code></dt>
<dd>Returns the data definition of either the specified field (use the first parameter, 'field', to specify), or of all available entities, from the master definition store.</dd>
<dt><code>importexportapi_set_def()</code></dt>
<dd>Allows you to make changes to the definition of any field in the master definition store.</dd>
<dt><code>importexportapi_get_field_type_info()</code></dt>
<dd>Returns all available information about all field types, or about the specified field type (if one is specified).</dd>
<dt><code>importexportapi_get_engines()</code></dt>
<dd>Returns all available information about all engines, or about the specified engine (if one is specified), or about engines of the specified type (if one is specified).</dd>
</dl>

<p>For examples of how to make use of these functions, refer to the importexportui module (currently included with the API - may be moved out to a separate project in future). Have fun using the API!</p>
