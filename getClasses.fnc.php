<?php

/* Get all Course Periods being taught by the From Teacher */

/* the general rosario functions do not persis into ajax called PHP as they are normally loaded through a warehouse function routine. So we have to get them back in play 
We could rebuild the database connection and the query call ability BUT then we need to modify it with every new module using AJAX
*/

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


$year = $_POST['year'];
$studentid = $_POST['studentid'];
$schoolid = $_POST['school_id'];
$sourceYear = $_POST['source_year'];


//$year = $_POST['year'];
//$grade = $_POST['grade'];
//$lastname = $_POST['lastname'];
//$schoolid = $_POST['school_id'];


/* === Find the students original course Periods   ====*/

$originalCourses = DBGet("Select s.COURSE_PERIOD_ID, s.COURSE_ID, cp.TITLE, c.TITLE AS SUBJECT
                        FROM schedule s,
                        course_periods cp,
                        courses c
                        where s.syear = cp.syear
                        AND s.school_id = cp.school_id
                        AND s.course_period_id = cp.course_period_id
                        AND cp.course_id = c.course_id
                        AND s.syear = c.syear
                        AND s.school_id = c.school_id
                        AND s.syear = '" . $sourceYear . "'
                        AND s.school_id = '" . $schoolid . "'
                        AND s.student_id = '" . $studentid . "'
                        ORDER BY c.TITLE, cp.TITLE ASC"
                    );
$selectCourses ='';
$connectstring = 'host=' . $DatabaseServer . ' ';

    if ( $DatabasePort !== '5432' )
    {
        $connectstring .= 'port=' . $DatabasePort . ' ';
    }

    $connectstring .= 'dbname=' . $DatabaseName . ' user=' . $DatabaseUsername;

    if ( $DatabasePassword !== '' )
    {
        $connectstring .= ' password=' . $DatabasePassword;
    }


foreach($originalCourses as $originalCourse){

        /*We need to run a recursion query here, we need to know what ID and name that course period
            goes by today. The key field is the COURSE_PERIOD_ID  There should be only 1 record that 
            can return*/

  
    $con = pg_connect( $connectstring );
     
$complimentQuery = "WITH RECURSIVE periods AS (
                        SELECT cp.COURSE_PERIOD_ID, cp.TITLE, cp.syear,cp.rollover_id,cp.school_id, cp.course_id
                        FROM course_periods cp
                       WHERE cp.COURSE_PERIOD_ID = '" . $originalCourse['COURSE_PERIOD_ID'] . "'
                    UNION ALL
                        SELECT cpB.COURSE_PERIOD_ID, cpB.TITLE, cpB.syear, cpB.rollover_id, cpB.school_id, cpB.course_id
                        FROM course_periods cpB
                        JOIN periods ON cpB.rollover_id = periods.COURSE_PERIOD_ID
                        )
                SELECT * 
                FROM periods
                WHERE syear = '" . $year . "'" ;

$complimentResult = pg_query($con, $complimentQuery) ;

$currentCourses = pg_fetch_all($complimentResult);



        //error_log(var_dump($currentCourses));

       foreach($currentCourses as $currentCourse){

             $selectCourses .= '<input type="checkbox" id="scheduleCourses[]" name="scheduleCourses[]" value="OLDPERIOD==>' .$originalCourse['COURSE_PERIOD_ID'] . '::NEWPERIOD==>' . $currentCourse['course_period_id'] . '::OLDCOURSE==>' . $originalCourse['COURSE_ID'] . '::NEWCOURSE==>' . $currentCourse['course_id'] . '"> PRIOR NAME  SUBJECT:: ' . $originalCourse['SUBJECT'] . ' COURSE:: ' . $originalCourse['TITLE'] . '<p>CURRENT NAME:: ' . $currentCourse['title'] .'</p></input></br></br>';

       }
       
 
    }





echo $selectCourses;


//=============================   functions =============================

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