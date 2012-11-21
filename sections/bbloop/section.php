<?php
/*
	Section: bbPressLoop
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: The Main Posts Loop. Required for BBPress
	Class Name: PageLinesBBLoop	
	Workswith: main
*/

class PageLinesBBLoop extends PageLinesSection {

   function section_template() { 

		while (have_posts()) :
			the_post();
			the_content();
		endwhile;
	}

}

/*
	End of section class
*/