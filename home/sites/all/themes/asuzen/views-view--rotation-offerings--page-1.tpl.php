<?php
// $Id: views-view.tpl.php,v 1.13.2.2 2010/03/25 20:25:28 merlinofchaos Exp $
/**
 * @file views-view.tpl.php
 * Main view template
 *
 * Variables available:
 * - $classes_array: An array of classes determined in
 *   template_preprocess_views_view(). Default classes are:
 *     .view
 *     .view-[css_name]
 *     .view-id-[view_name]
 *     .view-display-id-[display_name]
 *     .view-dom-id-[dom_id]
 * - $classes: A string version of $classes_array for use in the class attribute
 * - $css_name: A css-safe version of the view name.
 * - $css_class: The user-specified classes names, if any
 * - $header: The view header
 * - $footer: The view footer
 * - $rows: The results of the view query, if any
 * - $empty: The empty text to display if the view is empty
 * - $pager: The pager next/prev links to display, if any
 * - $exposed: Exposed widget form/info to display
 * - $feed_icon: Feed icon to display, if any
 * - $more: A link to view more, if any
 * - $admin_links: A rendered list of administrative links
 * - $admin_links_raw: A list of administrative links suitable for theme('links')
 *
 * @ingroup views_templates
 */
?>
<div class="<?php print $classes; ?>">
  <?php if ($admin_links): ?>
    <div class="views-admin-links views-hide">
      <?php print $admin_links; ?>
    </div>
  <?php endif; ?>
  <?php if ($header): ?>
    <div class="view-header">
      <?php print $header; ?>
    </div>
  <?php endif; ?>

  <?php if ($exposed): ?>
    <div class="view-filters">
      <?php print $exposed; ?>
    </div>
  <?php endif; ?>

  <?php if ($attachment_before): ?>
    <div class="attachment attachment-before">
      <?php print $attachment_before; ?>
    </div>
  <?php endif; ?>

  <?php if ($rows): ?>
    <div class="view-content">
      <?php print $rows; ?>
    </div>
  <?php elseif ($empty): ?>
    <div class="view-empty">
      <?php print $empty; ?>
    </div>
  <?php endif; ?>

  <?php if ($pager): ?>
    <?php print $pager; ?>
  <?php endif; ?>

  <?php if ($attachment_after): ?>
    <div class="attachment attachment-after">
      <?php print $attachment_after; ?>
    </div>
  <?php endif; ?>

  <?php if ($more): ?>
    <?php print $more; ?>
  <?php endif; ?>

  <?php if ($footer): ?>
    <div class="view-footer">
      <?php print $footer; ?>
    </div>
  <?php endif; ?>

  <?php if ($feed_icon): ?>
    <div class="feed-icon">
      <?php print $feed_icon; ?>
    </div>
  <?php endif; ?>
<?php 
/** This is a brutally hackish way of trying to automatically determine whether to allow students to apply for a research rotation or not. 
    It is based upon the stated Research Rotation policies of having a window open for application between the start of the semester, and one week after.
	Since Views does not provide a nice way of getting an object instance corresponding to a database row, I have to basically parse the generated output
	to find the value of the start date of the semester. This is likely to break if the output style changes. In fact, if we look at this as the "view" layer of a psuedo-MVC approach
	(Pseudo, As the giant flaming shitball that is Drupal is ANYTHING but MVC), this is completely the wrong place for this kind of logic. Unfortunately, Views does not provide much of a
    "Controller", and the use of Views Custom Field did not prove helpful. Quite frankly, this sucks shit. I'll be buying Code Offsets for this.
**/	
if($rows){ 
	$doc = DOMDocument::loadHTML($rows);
	$nav = new DOMXPath($doc);
	$results = $nav->query("*//span[@class='date-display-single']");
	if($results)
	{
		$startDate = $results->item(0)->nodeValue;
		$begin = new DateTime($startDate);
		$begin->modify("-1 week");
		$now = new DateTime();
		$oneWeekAfter = new DateTime($startDate);
		$oneWeekAfter->modify("+1 week");
		if($begin < $now && $now <= $oneWeekAfter )
		{ 
?>
<div id="applyRotation">
<span>Research Rotations</span>
<hr />
<a href="apply?semester=1343">View and Apply</a>
<script type="text/javascript">
                        Cufon.replace('span');
                        Cufon.now();
</script>
</div>
<?php }
}
}
?>
</div> <?php /* class view */ ?>
