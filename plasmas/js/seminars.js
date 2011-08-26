/* Author: Nicholas Vaidyanathan
   Description: A set of scripts to pull the JSON feed from 
   http://physics.asu.edu/seminar/panels
   process them, and display them in a scrolling manner on an html page
*/

jQuery.displaySeminar = function (val)
{
	jQuery("h2").html(val.seminar.Type);
	jQuery("#title").html(val.seminar.Title);
	jQuery("#speaker").html(val.seminar.Name);
	if(val.seminar.Institution != "")
	{
		jQuery("#institution").html(val.seminar.Institution);
		jQuery("#affiliation").show();
	}
	jQuery("#date").html(val.seminar.Date);
	jQuery("#location").html(val.seminar.Location);
	jQuery("#host").html(val.seminar.Host);
	jQuery("#abstract").html(val.seminar.Abstract);
}

jQuery(document).ready(function() {
		// get the json feed from the Views Datasource set up in Drupal for all faculty members
		jQuery.getJSON('http://physics.asu.edu/seminar/panels', function(data){
			var i = 0;
			var seminars = data.seminars;
			window.setInterval(function(){
				jQuery.displaySeminar(seminars[i++]);
				jQuery("#flash").hide();
				i = i == seminars.length ? 0 : i; // loops the interval
			}, 7000);
		});
});






















