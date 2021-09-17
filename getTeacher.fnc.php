<?php

/* Get all Course Periods being taught by the From Teacher */

/* the general rosario functions do not persis into ajax called PHP as they are normally loaded through a warehouse function routine. So we have to get them back in play 
We could rebuild the database connection and the query call ability BUT then we need to modify it with every new module using AJAX
*/

$teacher = $_POST['info'];
$schoolYear = $_POST['year'];
$schoolid = $_POST['schoolid'];

/**
 * Load functions
 */
/**
 * Include config.inc.php file.
 *
 * Do NOT change for require_once, include_once allows the error message to be displayed.
 */

if ( ! include_once '../../config.inc.php' )
{
	die( 'config.inc.php file not found. Please read the installation directions.' );
}

require_once '../../database.inc.php';

$functions = glob( '../../functions/*.php' );

foreach ( $functions as $function )
{
	require_once $function;
}
//============================  Code below / Setup Above ====================

if($teacher == 'NO'){
		//Nothing is going to happen
}else{

	$courseCheckboxes ='';
	$coursesTable = '<table>';



	$fromTeacherCourses = DBGET("SELECT cp.*, c.TITLE As COURSE_TITLE
		FROM public.course_periods cp,
		public.courses c
		WHERE cp.SCHOOL_ID = '" . $schoolid ."'
		AND cp.SYEAR = '" . $schoolYear . "'
		AND cp.TEACHER_ID = '" . $teacher . "'
		AND cp.COURSE_ID = c.COURSE_ID
	ORDER BY c.TITLE, cp.Title ASC ");


	foreach($fromTeacherCourses as $course){
		$courseCheckboxes .= '<tr><td><input type="checkbox" name="courses[]" value="' . $course['COURSE_PERIOD_ID'] . '" "checkox_value">' . $course['COURSE_TITLE'] . ' -- ' .$course['TITLE'] . ' </input></td></tr> ';
	}

	$coursesTable .= $courseCheckboxes . UserSchool() . '   ' . UserSYear() . '</table>'; 

	echo $coursesTable;
}

/*
 * @param  mixed &$var    Variable.
 * @param  mixed $default Default value if undefined. Defaults to null.
 * @return mixed Variable or default.
 */
function issetVal( &$var, $default = null )
{
	return ( isset( $var ) ) ? $var : $default;
}

?>