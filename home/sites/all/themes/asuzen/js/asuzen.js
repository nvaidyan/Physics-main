/**
*  Title: ASU Zen Theme
*  Copyright: (c) 2008 Arizona State University
*  Author: Jeff Beeman, Kathy Marks, Nathan Gudmunson
*  Description: Custom javascript
*/

jQuery(document).ready(function(){
   setupEventHandlers();
   if(shouldDisplayFacultyBlocks())
   { 
        showDefaultListing();
   }
});

function shouldDisplayFacultyBlocks()
{
   var currentPage = window.location.href;
   var facultyURLs = "people/.*faculty.*";
   var academicProfs = "people/academic-professional.*";
   return (currentPage.match(facultyURLs) || currentPage.match(academicProfs)) && currentPage != "http://physics.asu.edu/home/people/faculty";
}

function setupEventHandlers()
{
   jQuery(".alphabetical-faculty-sort #facultySorter").click(function(){
      jQuery(".group-faculty-sort").show();
      jQuery(".alphabetical-faculty-sort").hide();
   });
   jQuery(".group-faculty-sort #facultySorter").click(function(){ 
      jQuery(".alphabetical-faculty-sort").show();
      jQuery(".group-faculty-sort").hide();
   });
}

function showDefaultListing() {
      var alpha = jQuery(".alphabetical-faculty-sort");
      var group = jQuery(".group-faculty-sort");
      
      if (isHidden(alpha) && isHidden(group)) {
         alpha.show();
      }
}

function isHidden(htmlElement){
  return htmlElement.css("display") == "none";
}
