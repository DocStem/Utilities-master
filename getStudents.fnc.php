<?php

/* Get all Course Periods being taught by the From Teacher */

/* the general rosario functions do not persis into ajax called PHP as they are normally loaded through a warehouse function routine. So we have to get them back in play 
We could rebuild the database connection and the query call ability BUT then we need to modify it with every new module using AJAX
*/

$year = $_POST['year'];
$grade = $_POST['grade'];
$lastname = $_POST['lastname'];
$schoolid = $_POST['school_id'];
$type = $_POST['returnType'];

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



/* Determine the items in the where clause know that year is required */

if(! empty($year)){
	$whereClause = " AND se.SYEAR = '" . $year . "'";
}

if(! empty($grade) && $grade != 'NO'){
	$whereClause .= " And se.GRADE_ID = '" . $grade . "'";
}

if(! empty($lastname)){
	$whereClause .= " And s.LAST_NAME LIKE '%" . $lastname . "%'";
}


	$students =	DBGET("Select s.*
		  From students s,
		  student_enrollment se
		  Where s.STUDENT_ID = se.STUDENT_ID
		  AND se.DROP_CODE IS NULL
		  AND se.END_DATE IS NULL
		  AND se.SCHOOL_ID = '" . $schoolid . "'"
		  . $whereClause
		);


//easier for speed to do the if outside the loop
	if($type == "selectOption"){
		foreach($students as $student){
			$name = trim($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']);

			$createFromSelect .= '<option value="' . 
								$student['STUDENT_ID'] .'">'  . $name . '</option>';
		}
	}else{
		foreach($students as $student){
			$name = trim($student['FIRST_NAME'] . ' ' . $student['LAST_NAME']);

			 $createFromSelect .= '<input type="checkbox" id="targetStudents[]" value="' .$student['STUDENT_ID'] . '">' . $name . '</input></br>';
		}
	}
	

echo $createFromSelect;

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