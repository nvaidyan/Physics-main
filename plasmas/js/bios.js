/* Author: Nicholas Vaidyanathan
   Description: A set of scripts to pull the JSON feed from 
   http://physics-dev.asu.edu/faculty/profiles
   process them, and display them in a scrolling manner on an html page
*/

jQuery(document).ready(function() {
		var faculty = getAllFaculty();
		slideshowDisplay(faculty);
});

function getAllFaculty() {
	// get the json feed from the Views Datasource set up in Drupal for all faculty members
	var faculty = []
	jQuery.ajax({
		url:'http://physics.asu.edu/faculty/all',
		dataType:'json',
		async:false,
		success: function(data){
			faculty = data.Faculty;
		},
		statusCode:{
			500: function(jqXHR, textStatus, errorThrown){
			alert("Got an error. The status was " + textStatus + " the error thrown was " + errorThrown + " jqHR " + jqXHR.toString());
			}
		}
	});
	return faculty;
}

function slideshowDisplay(faculty) {
 var flash = jQuery("#flash");
 var i =0;
 window.setInterval(function(){
	var facultyMember = (i< faculty.length) ? faculty[i].professor.title : faculty[0].professor.title;
	retrieveProfile(facultyMember);
	flash.hide();
	i = (i >= faculty.length) ? 0 : i+1; // loops the interval
	}, 7000);
}

function retrieveProfile (faculty)
{
	jQuery.ajax({
		url: 'http://physics.asu.edu/faculty/profiles/' + faculty,
		dataType: 'json',
		success: function(data){displayProfile(data);}
		});
}

function displayProfile(data)
{
   //we know that we get a {"profiles": [{"profile":{...}}] response here
				jQuery.each(data.profiles, function(key, val) {
					// now populate the data from the profile JSON object
					jQuery("#profile h2").html(val.profile.Name);
					jQuery("#deptTitle").html(val.profile.Title);
					jQuery("#degreeInfo").html(val.profile.Degree);
					jQuery("#homeDept").html(val.profile.HomeDept);
					jQuery("#areaStudy").html(val.profile.Area);
					jQuery("#office").html(val.profile.Office);
					jQuery("#officeHours").html(val.profile.Hours);
					jQuery("#phone").html(val.profile.Phone);
					jQuery("#email").html(val.profile.Email);
					jQuery("#background").html(val.profile.Background);
					jQuery("#profileImage").attr('src', val.profile.Picture).attr('alt', "A picture of " + val.profile.Name);
					displayPublications(val.profile.ProfId);
					displayResearchInterests(val.profile.ProfId);
					
				});
}

function displayPublications(profId){
	jQuery.ajax({
		url: 'http://physics.asu.edu/home/faculty/' + profId + '/publications', 
		dataType: 'json',
		async: false,
		success:function(newData, newStatus) {
			var pubList = jQuery("#publications ul");
			pubList.html(""); // clear previous prof's pubs
			if(newData.publications.length == 0)
			{
				pubList.append('<li>No publications currently added</li>');
			}
			else
			{
				jQuery.each(newData.publications, function(key2, val2) {
					pubList.append('<li>' + val2.publication.Authors + '.' + val2.publication.Year + '.' + val2.publication.Title + '.' + val2.publication.Journal + '.' + val2.publication.Volume + '.' + val2.publication.Pages + '</li>');
				});
			}
		}	
	});
}

function displayResearchInterests(profId) {
	jQuery.ajax({
		url: 'http://physics.asu.edu/home/faculty/' + profId + '/researchpics', 
		dataType: 'json',
		async: false,
		success:function(newData, newStatus) {
			var researchList = jQuery(".slideshow");
			researchList.html(""); // clear previous prof's interests
			if(newData.research_interests.length == 0)
			{
				researchList.append('<p>No research interests currently added</p>');
			}
			else
			{
				jQuery.each(newData.research_interests, function(key2, val2) {
					researchList.append('<div><img src="' + 
					val2.research_interest.field_research_area_image_fid + 
					'" alt="research image" width="200" height="200" /><p>' + 
					val2.research_interest.body + '</p></div>');
				});
				jQuery('.slideshow').cycle({
					speed: 1500,
					timeout: 1000
					});
			}
		}
	});
}