<?php
/*
	Section: BB PostLoop
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: The Main Posts Loop. Required for BBPress
	Class Name: PageLinesBBLoop	
	Workswith: main
*/

class PageLinesBBLoop extends PageLinesSection {

   function section_template() { 

		the_content();
	}

}

/*
	End of section class
*/