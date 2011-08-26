<?php
// $Id: views-slideshow.tpl.php,v 1.1.2.2.2.4 2010/02/23 22:49:57 psynaptic Exp $

/**
 * @file
 * Default views template for displaying a slideshow.
 *
 * - $view: The View object.
 * - $options: Settings for the active style.
 * - $rows: The rows output from the View.
 * - $title: The title of this group of rows. May be empty.
 *
 * @ingroup views_templates
 */
?>

<?php print $slideshow; ?>
<html>
<head>
<meta http-equiv="refresh" content="30; url=seminar.php?g=<?php echo $val; ?>" />
<link href="dbb.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="header">
	<div id="headerBkgd"></div>
	<div id="headerText">
		<img src="images/dept_banner.png" style="position: relative;top:5px;" />
	</div>
</div>
<div id="content">
	<div id="center">
		<table class="box" style="width:100%;">
			<tr>
				<td class="box_ul"></td>
				<td class="box_top" style="padding-left:10px;">
					<p class="boxHeader">Physics News</p>
				</td>
				<td class="box_ur"></td>
			</tr>
			<tr>
				<td class="box_left"></td>
				<td class="box_main">
					<span class="opaque">
						<table style="width:100%;">
							<tr>
								<td class="newsText" style="padding:10px;vertical-align:top;">
								<?php
								/*if ($news[$ref][4] != -1) {					// If there is an image associated with the news item...
									$photo = get('CMS','files','','id_file='.$news[$ref][4]);
								?>
								<div class="photo" style="float:right;padding:0 0 5px 10px;width:250px;">
									<img src="../images/news/<?php echo $photo[0][1]; ?>" width="240" />
									<br /><?php echo $news[$ref][5]; ?>
								</div>
								<?php } */?>
								<p class="subhead"><?php /* echo $news[$ref][2]; ?></p>
								<p class="newsText">
									<?php echo substr($news[$ref][3],0,1600);
									if (strlen($news[$ref][3]) > 1600) {
										echo "...<br /><br />For more information, see our web site at http://physics.asu.edu";
									} */ dsm($node);?>
								</p>
								</td>
							</tr>
						</table>
					</span>
				</td>
				<td class="box_right"></td>
			</tr>
			<tr>
				<td class="box_ll"></td><td class="box_bottom"></td>
				<td class="box_lr"></td>
			</tr>
		</table>
	</div>
</div>
</body>
</html>