<?php
/**
 * @file node.tpl.php
 *
 * Theme implementation to display a node.
 *
 * Available variables:
 * - $title: the (sanitized) title of the node.
 * - $content: Node body or teaser depending on $teaser flag.
 * - $picture: The authors picture of the node output from
 *   theme_user_picture().
 * - $date: Formatted creation date (use $created to reformat with
 *   format_date()).
 * - $links: Themed links like "Read more", "Add new comment", etc. output
 *   from theme_links().
 * - $name: Themed username of node author output from theme_user().
 * - $node_url: Direct url of the current node.
 * - $terms: the themed list of taxonomy term links output from theme_links().
 * - $submitted: themed submission information output from
 *   theme_node_submitted().
 *
 * Other variables:
 * - $node: Full node object. Contains data that may not be safe.
 * - $type: Node type, i.e. story, page, blog, etc.
 * - $comment_count: Number of comments attached to the node.
 * - $uid: User ID of the node author.
 * - $created: Time the node was published formatted in Unix timestamp.
 * - $zebra: Outputs either "even" or "odd". Useful for zebra striping in
 *   teaser listings.
 * - $id: Position of the node. Increments each time it's output.
 *
 * Node status variables:
 * - $teaser: Flag for the teaser state.
 * - $page: Flag for the full page state.
 * - $promote: Flag for front page promotion state.
 * - $sticky: Flags for sticky post setting.
 * - $status: Flag for published status.
 * - $comment: State of comment settings for the node.
 * - $readmore: Flags true if the teaser content of the node cannot hold the
 *   main body content.
 * - $is_front: Flags true when presented in the front page.
 * - $logged_in: Flags true when the current user is a logged-in member.
 * - $is_admin: Flags true when the current user is an administrator.
 *
 * @see template_preprocess()
 * @see template_preprocess_node()
 */
?>
<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>"><div class="node-inner">

  <?php print $picture; ?>

  <?php if (!$page): ?>
    <h2 class="title">
      <a href="<?php print $node_url; ?>" title="<?php print $title ?>"><?php print $title; ?></a>
    </h2>
  <?php endif; ?>

  <?php if ($unpublished): ?>
    <div class="unpublished"><?php print t('Unpublished'); ?></div>
  <?php endif; ?>

  <div class="content seminar">
    <?php // print $content; ?>
	<div class="series-info">
		<?php print $field_seminar_type_rendered . $field_semester_reference_rendered ?>
	</div>
        <script type="text/javascript">
		Cufon.replace(".field-field-seminar-type");
		Cufon.replace(".field-field-semester-reference");
		Cufon.now();
        </script>
	<hr />
	<div class="seminar-info vevent">
		<div class="summary"><?php print $field_name_rendered; ?></div>
		<?php print $field_other_affiliations_rendered; ?>
		<div class="dtstart"><?php print $field_seminar_date_rendered; ?></div>
		<div class="location"><?php print $field_seminar_location_rendered; ?></div>
                <!-- print $field_host_rendered -->
		<div class="host">
                    <?php 
                       $view_name = "full_name_for_seminars";
                       $display_id = "default";
                       $my_args = $node->field_host[0][uid];
                       print views_embed_view($view_name,$display_id,$my_args);  
                    ?>
                </div>
		<h3>Abstract</h3>
		<?php print $node->content['body']['#value']; ?>
	</div>
   </div>

   <div id="meetingLinks">
	 <?php 
	    /* Would be useful if we decided to use our internal Sharepoint calendars for meeting scheduling, which I still believe is the best
		*  solution. However, currently Faculty are tied to the old site's way fo doing it, so we've replicated that in Drupal
		global $user;
		$permittedRoles = array('Faculty', 'administrator');
		$output = "";
		$check = array_intersect($permittedRoles, array_values($user->roles));
		if(!empty($check)) {
			$seminar_type = $node->field_seminar_type[0]['value'];
			switch($seminar_type)
			{
				case 'Department Colloquia':
					$output = "https://physics.sharepoint.asu.edu/dept_info/Lists/Department Colloquia/calendar.aspx";
					break;
				case 'Biophysics Seminar':
					$output = "https://physics.sharepoint.asu.edu/dept_info/Lists/Biophysics Seminar/calendar.aspx";
					break;
				case 'Nanoscience Seminar':
					$output = "https://physics.sharepoint.asu.edu/dept_info/Lists/Nanoscience Seminars/calendar.aspx";
					break;
				case 'Particle/Astrophysics Seminar':
					$output = "https://physics.sharepoint.asu.edu/dept_info/Lists/Particle Astro Seminars/calendar.aspx";
					break;
				default: $output="#";
			}
			print "<a href='$output' target='_blank' title='To book, please contact the seminar coordinator' style='font-weight:bold'>View this speaker's meeting availability.</a>";
		}*/
		$permittedRoles = array('Faculty', 'administrator');
		$check = array_intersect($permittedRoles, array_values($user->roles));
		if(!empty($check)) {
				print "<h3>Booked meetings with the seminar speaker are shown below</h3>";
				print views_embed_view('seminar_meeting','default',array($node->nid));
		}
	?>
   </div>
  <?php print $links; ?>

</div></div> <!-- /node-inner, /node -->
