
Module: Field Actions
Requires: Actions module and CCK

OVERVIEW:

The Field Actions module combines the power of Drupal actions with the data in
CCK nodes to create new ways of working with your nodes in Drupal. This module
enables action enabled modules (such as workflow.module) to reach into your
nodes, pull out information, and use it appropriately. This leads to more reuse
of data throughout your site, and easier creation of behavior for
non-programmers and programmers alike.

INSTALLATION:

Download and install the actions and CCK modules. You may also wish to install
the workflow module, as actions have no user interface of their own.

http://drupal.org/project/actions
http://drupal.org/project/cck
http://drupal.org/project/workflow

Enable these modules on your modules adminstration page, and then enable this
module.

CURRENT ACTIONS:

User Reference Actions:
- Send an email to a user reference field
- Assign ownership of a node to a user reference field

USAGE:

Create a node type that uses any of the fields listed in the Current Actions
section of this file.
 
Visit yousite.com/admin/actions and select the appropriate action from the drop
down list. Choose the right field from your list of options, complete the form,
and save your action. It is now ready to use in a workflow or other action
enable module.

TO DO:

1. Add actions for other CCK fields. Some plans include:
 - Apply action to node referenced by field - apply an action to every node
listed in a node reference field. Additionally, do the inverse, apply action to
every node that references a the acted on node from a given field.
 - GET or POST data from/to a given web url field
 - Combine fields to work in tandem

2. Define a field that contains a UI for invoking actions on certain events
(i.e. submit/save, new revision, cron interval, etc). 
