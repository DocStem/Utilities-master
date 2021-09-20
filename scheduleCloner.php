<?php



DrawHeader( ProgramTitle() );

if( User( 'PROFILE' ) === 'admin'){
$canRun = true;
		$warning = '';

	//See if it time to save anything and Generate the new schedules
	//echo('</pre>' . print_r($_REQUEST) . '</pre>');

	if($_REQUEST['Generate'] == 'Generate'){
		//echo('</pre>' . print_r($_REQUEST) . '</pre>');
		//The last table is where any output from this click is going to write to. outputGenerate
//echo( 'from year ' . $_REQUEST['fromYear']);
		testGenerateStatus($canRun,$warning);
		

	}else{
		$canRun = false;
	}


		//echo('<pre>' . print_r($_REQUEST) . '</pre>');

		$info = 'This routine clones a current or past student SCHEDULE to current Active Student(s).</br> 
			The schedule shown for Clone Source Student will have both the original schedule Period names (if in the past) and the current Period names for identification purposes.</br></br>
			<b> Make a system database backup prior to running the utility in the advent you make a mistake </b></br>
			This module is not going to validate the number of students in the class or for conflicts of period.</br></br>
			Step 1 : Chose filters to reduce the number of possible students as clone source.</br>
			Step 2 : Click the class periods of the Clone Source or Select All</br>
			Step 3 : Filter for Target Clone Students</br>
			Step 4 : Click Target Students</br>
			Step 5 : Process Schedules.(Generate Schedule) Last Step</br></br>';

		$instructions = '<div class="table-responsive"><table style=width:80%;align:center><tr><td>' . $info . '</td></tr></table></div>';


		echo $instructions;

		/* Get the school name */
		$schoolName = DBGET("SELECT TITLE
			   FROM SCHOOLS
			   WHERE ID = '" . UserSchool() . "'
			   AND SYEAR = '" . UserSYear() . "'");


		// =================  Queries that can be run in advance  ===============
		$schoolYears = DBGET("SELECT SYEAR
			   FROM SCHOOLS
			   WHERE ID = '" . UserSchool() . "'");


		$studentGrades = DBGET("SELECT ID, TITLE FROM 
			school_gradelevels
			WHERE SCHOOL_ID = '" . UserSchool() . "' 
			ORDER BY TITLE ASC");




		// ===================    Mock Up Screen =============================================


		echo '<form action="'. URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=submit') . '" method="POST">';


		$actionBar = '<button type="submit" name="Generate" value="Generate">Generate Schedules</button></br></br>';
		//echo($actionBar);

		$actionBar = '<div class="table-responsive"><table style=width:80%;align:center><tr><td>' . $actionBar . '</td></tr></table></div>';

		echo($actionBar);

		//The source from which data will come
		$createFromYear = '<select id="fromYear" name="fromYear" style="width:400px;")">';
		$createFromYear .= '<option value="NO" selected>   --- Select Clone Year Required ---   </option>';

		$createFromYear .= makeOptionSelects($schoolYears,'SYEAR');

		$createFromYear .= '</select></br>';


	
		$createFromGrade = '<select id="fromGrade" name="fromGrade" style="width:400px;")">';
		$createFromGrade .= '<option value="NO" selected>   --- Grade At Time of Schedule ---   </option>';

		$createFromGrade .= makeOptionSelects($studentGrades,'ID','TITLE');

		$createFromGrade .= '</select></br>';

		

		//Change this to a text box and use a LIKE

		//Change this to a text box and use a LIKE
		$createFromLastName = '<input type="text" id="fromLastName" name="fromLastName" placeholder="Last Name"<br>';

		

		$createFromGoButton =  '<button type="button" value="Sources" onclick="getStudents(this.value,' . UserSchool() .',null)">Populate Possible Sources</button></br></br>';

		
		// === From sources is the creation of a possible list of students 
		$createFromSources = '<select id="fromSources" name="fromSources" style="width:400px;" onchange="myName(this.value,' . UserSyear() . ',' . UserSchool(). ')">';
		$createFromSources .= '<option value="NO" selected>   --- Possible Students ---   </option>';
		
		$createFromSources .= '</select></br>';


//===============================  Data Target  =====================================

		$createToGrade = '<select id="ToGrade" name="ToGrade" style="width:400px;">';
		$createToGrade .= '<option value="NO" selected>   --- Current Grade ---   </option>';

		$createToGrade .= makeOptionSelects($studentGrades,'ID','TITLE');

		$createToGrade .= '</select></br>';

		$createToLastName = '<input type="text" id="ToLastName" name="ToLastName" placeholder="Last Name"<br>';

		$createToGoButton =  '<button type="button" value="Targets" onclick="getStudents(this.value,' . UserSchool() . "," . UserSyear() . ')">List Targets</button>';


		$cloneFromTable = '<table style="width:65%"><tr><td>' . $createFromYear . $createFromGrade . $createFromLastName . $createFromGoButton . $createFromSources . '</td></tr></table>';

	
		$cloneToTable = '<table style="width:35%"><tr><td>' . $createToYear . $createToGrade . $createToLastName . $createToGoButton . '</td></tr></table>';

		
		$headerTable = '<table border="1" style="width:80%"><tr><td>' . $cloneFromTable . '</td><td>' . $cloneToTable . '</td></tr>';

		 $headerTable .= '<tr><td valign=top><div id="fromClasses"><div></td>';

		$headerTable .= '<td valign=top><div id="ToStudents"></div></td></tr></table>';

		echo $headerTable;

		echo('</form>');

// ==============================  Generation Output  ==================================
?>
	<table>
		<tr><td><div id="outputGenerate">

<?php echo( $warning);

	if($canRun){
		foreach($_REQUEST['targetStudents'] as $targetStudent){
			echo(' have a number ' . $targetStudent);

			foreach($_REQUEST['scheduleCourses'] as $course){
				/* sets breaks down into
				0 = OLDPERIOD
				1 = NEWPERIOD
				2 = OLDCOURSE
				3 = NEWCOURSE
				*/
				$sets = explode('::',$course);
				$oldPeriod = explode('==>',$sets[0]);
				$newPeriod = explode('==>',$sets[1]);
				$oldCourse = explode('==>',$sets[2]);
				$newCourse = explode('==>',$sets[3]);

				echo(' have a course ' . $sets[0]);
				//Get the OLD Record to act as a template that will be modified
				$oldRecord = DBGET("Select *
							From schedule
							Where course_period_id = '" . $oldPeriod[1] . "'
							AND course_id = '" . $oldCourse[1] . "'
							AND STUDENT_ID = '" . $_REQUEST['fromSources'] . "'"
						);

				//echo('<pre>' . print_r($oldRecord,true) . '</pre>');

				//Validate that a new record does not exist
				$newRecord = DBGET("Select *
							From schedule
							Where course_period_id = '" . $newPeriod[1] . "'
							AND course_id = '" . $newCourse[1] . "'
							AND STUDENT_ID = '" . $targetStudent . "'"
						);

				//echo('<pre>' . print_r($newRecord,true) . '</pre>');

				if(empty($newRecord)){
					/*Update the oldrecord fields to make them into what we need for this new year,
					new record, new student 
					Fields we want to update are 
					SYEAR
					STUDENT_ID
					START_DATE == YYYY-MM-DD
					COURSE_ID
					COURSE_PERIOD_ID

					*/
					echo('</br></br>Scheduling Student ID: ' . $targetStudent . '</br>');
					echo("       CLASS ID: " . $newPeriod[1] . '</br>');

					$oldRecord[1]['SYEAR'] = UserSyear();
					$oldRecord[1]['STUDENT_ID'] = $targetStudent;
					$oldRecord[1]['COURSE_ID'] = $newCourse[1];
					$oldRecord[1]['COURSE_PERIOD_ID'] = $newPeriod[1];
					$oldRecord[1]['START_DATE'] = '2021-08-30';
					$oldRecord[1]['CREATED_AT'] = NULL;

					//echo('<pre>' . print_r($oldRecord,true) . '</pre>');

					//Time to run an insert QUERY
					DBQuery("INSERT INTO SCHEDULE
						(SYEAR,SCHOOL_ID,STUDENT_ID,START_DATE,COURSE_ID,COURSE_PERIOD_ID,MP,MARKING_PERIOD_ID) 
						VALUES(" . $oldRecord[1]['SYEAR'] . "," . 
								$oldRecord[1]['SCHOOL_ID'] . "," .
								$oldRecord[1]['STUDENT_ID'] . ",'" . 
								$oldRecord[1]['START_DATE'] . "'," . 
								$oldRecord[1]['COURSE_ID'] . "," . 
								$oldRecord[1]['COURSE_PERIOD_ID'] . ",'" . 
								$oldRecord[1]['MP'] . "','12')"
					);


				}//checking and updating oldRecord with new
			}//Looking at each course cycling by each target student
			
		}//Looking at each target student
	}// canRun is true and we process this

?>
		</div></td></tr>

	</table>		

<?php

}else{ //your not an admin so go away.

	$info = 'This is an administrative function only</br></br>';

		$instructions = '<div class="table-responsive"><table style=width:80%;align:center><tr><td>' . $info . '</td></tr></table></div>';


		echo $instructions;

}




//Test to see if we can do anything after Generate is hit.. Check all the input and output error messages.
function testGenerateStatus(&$canRun,&$warning){
	//This needs to become a function of all possible warnings.
		if($_REQUEST['fromYear'] == 'NO'){
			
			$warning .= 'No Source Year Selected</br>';
			$canRun = false;
		}
		if($_REQUEST['fromGrade'] == 'NO'){
			$warning .= 'No Source Grade Selected</br>';
			$canRun = false;
		}
		
		if($_REQUEST['fromSources'] == 'NO'){
			$warning .= 'No Source Student Selected</br>';
			$canRun = false;
		}
		if($_REQUEST['ToGrade'] == 'NO'){
			
			$warning .= 'No Target Grade Selected</br>';
			$canRun = false;
		}
		
		if(empty($_REQUEST['fromSources']) || ! isset($_REQUEST['fromSources'])){
			$warning .= 'No Source Courses Selected</br>';
			$canRun = false;
		}

		 if(empty($_REQUEST['targetStudents'])){
		 	$warning .= 'No Target Students Selected</br>';
		 	$canRun = false;
		 }
}	

function makeOptionSelects($singleArray,$values, $words = NULL){
$Select = '';

if($words == NULL){
	$words = $values;
}

	foreach($singleArray as $record){
		$Select .= '<option value="' . 
							$record[$values] .'">'  . $record[$words] . '</option>';
	}
	
	return $Select;

}

?>

<script type="text/javascript">
	
function getStudents(sourceOrTarget,schoolid,currentyear){
	//alert(sourceOrTarget);

	if(sourceOrTarget == "Sources"){
		var elem = 'from';
		var selectYear = $('#' + elem + 'Year').val(); // en
		var type = 'selectOption';
	}else{
		var elem = 'To';
		var selectYear = currentyear;
		var type = 'checkbox';
		
	}

	//alert(elem + '  ' + selectYear);

	//var selectYear = $('#' + elem + 'Year').val(); // en
	var selectGrade = $('#' + elem + 'Grade').val(); // en
	var textLastName = $('#' + elem + 'LastName').val(); // English

	

	$.ajax({
            url: 'modules/Utilities/getStudents.fnc.php',
            type: 'POST',
            //dataType: 'JSON',
            data:{year: selectYear,
            	  grade: selectGrade,
            	  lastname: textLastName,
            	  school_id: schoolid,
            	  returnType: type},	
            success: function(response){
                 //Log the data to the console so that
                //you can get a better view of what the script is returning.
                //console.log(response);


			if(sourceOrTarget == "Sources"){
               $('#' + elem +'Sources').html(response);
           }else{
           	//alert(response);
        		var studentList = 
           	$('#ToStudents').html(response);
           }

            },
		     error:function(x,e) {
			    if (x.status==0) {
			        alert('You are offline!!\n Please Check Your Network.');
			    } else if(x.status==404) {
			        alert('Requested URL not found.');
			    } else if(x.status==500) {
			        alert('Internel Server Error.');
			    } else if(e=='parsererror') {
			        alert('Error.\nParsing JSON Request failed.');
			    } else if(e=='timeout'){
			        alert('Request Time out.');
			    } else {
			        alert('Unknow Error.\n'+x.responseText);
			    }
			}
        });

	//alert(selectYear + selectGrade);

}

function myName(val,syear,schoolid) {
	// body...
	//alert(val);
var sourceYear = $('#fromYear').val(); // en


//alert(sourceYear + ' ' + syear + ' ' + schoolid + ' ' + val);
	$.ajax({
            url: 'modules/Utilities/getClasses.fnc.php',
            type: 'POST',
            //dataType: 'JSON',
            data:{year: syear,
            	  source_year: sourceYear,
            	  studentid: val,
            	  school_id: schoolid},	
            success: function(response){
                 //Log the data to the console so that
                //you can get a better view of what the script is returning.
                //console.log(response);


               $('#fromClasses').html(response);

            },
		     error:function(x,e) {
			    if (x.status==0) {
			        alert('You are offline!!\n Please Check Your Network.');
			    } else if(x.status==404) {
			        alert('Requested URL not found.');
			    } else if(x.status==500) {
			        alert('Internel Server Error.');
			    } else if(e=='parsererror') {
			        alert('Error.\nParsing JSON Request failed.');
			    } else if(e=='timeout'){
			        alert('Request Time out.');
			    } else {
			        alert('Unknow Error.\n'+x.responseText);
			    }
			}
        });

	//alert(selectYear + selectGrade);



}

</script>