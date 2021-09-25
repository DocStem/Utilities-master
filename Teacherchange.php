<?php



DrawHeader( ProgramTitle() );

if( User( 'PROFILE' ) === 'admin'){
		//echo('<pre>' . print_r($_REQUEST) . '</pre>');

		$info = 'This routine changes the currently assigned teacher of course periods to another in bulk. You do this routine for the School YEAR in Effect. If you are reassigning at the start of a year due to retirement or resignation, make sure to do the Roll Year Utility First. Otherwise you effect history. </br> <b>** A backup of the database prior to this step is highly recommended.</b> </br></br>
			This module is not going to validate the teacher schedule for conflicts of period.</br></br>
			Step 1 : Chose the teacher whose classes will be reassigned </br>
			Step 2 : Click the checkbox next to the classes that will be reassigned </br>
			Step 3 : Chose the new staff member that will teach that classes</br>
			Step 4 : Hit the REASSIGN Button</br></br>';

		$instructions = '<div class="table-responsive"><table style=width:80%;align:center><tr><td>' . $info . '</td></tr></table></div>';


		echo $instructions;

		/* Get the school name */
		$schoolName = DBGET("SELECT TITLE
			   FROM SCHOOLS
			   WHERE ID = '" . UserSchool() . "'
			   AND SYEAR = '" . UserSYear() . "'");

		//echo ('<pre>' . print_r($schoolName,true) . '</pre>');


		if( $_REQUEST['submit']['save'] && AllowEdit()){
			
			//Run the proper validations

			$canRun = true;

			if($_REQUEST['fromTeacher'] == 'NO'){
				$mesg .= " You must select a Teacher whose classes will be reassigned.</br>"; 
				$canRun = false;
			}
			if($_REQUEST['toTeacher'] == 'NO'){
				$mesg .= " You must select a Teacher to reassign the classes to.</br>"; 
				$canRun = false;
			}
			if(! $_REQUEST['courses']){
				$mesg .= " No Course Periods Selected.</br>"; 
				$canRun = false;
			}

			/* Check we have what we need and then do Updates */
			if($canRun){

				// Convert the names
				$fromTeacherName_RET = getTeacherInfo($_REQUEST['fromTeacher']);
				//echo ('<pre>' . print_r($fromTeacherName_RET,true) . '</pre>');

				$toTeacherName_RET = getTeacherInfo($_REQUEST['toTeacher']);

				$fromTeacherName = trim(makeName($fromTeacherName_RET[1]['FIRST_NAME'],$fromTeacherName_RET[1]['MIDDLE_NAME'],$fromTeacherName_RET[1]['LAST_NAME']));

				$toTeacherName = trim(makeName($toTeacherName_RET[1]['FIRST_NAME'],$toTeacherName_RET[1]['MIDDLE_NAME'],$toTeacherName_RET[1]['LAST_NAME']));

				echo '<div><p style=color:red>Running</p></div>';
				$updateCourses = $_REQUEST['courses'];

				$a=0;
				foreach($updateCourses as $updating){
					//echo $updateCourses[$a] . '  ';

					//Going to need to run the updates. 
			
						DBQuery("UPDATE PUBLIC.COURSE_PERIODS
								SET TEACHER_ID = '" . $_REQUEST['toTeacher'] . "',
								TITLE = REPLACE(TITLE,'" . $fromTeacherName . "','" . $toTeacherName . "')
								WHERE COURSE_PERIOD_ID = " . $updateCourses[$a]
							);
				


					$a++;
				}
			}else{
				echo '<div><p style=color:red>' . $mesg . '</p></div>';
			}

			


		}else{
			//echo 'Do NOTHING' about an update. Just continue to show form;
		}

		echo " <div><p>Your Making Changes for <b>" . $schoolName[1]['TITLE'] . "</b> --  School Year :<b>" . UserSyear() . '</b></p></div>';

		/* List of teachers 
		Pull the list from Staff and check for Profile teacher

		*/


		$teachers = getTeacherInfo('ALL');


		//echo ('<pre>' . print_r($teachers,true) . '</pre>');


		echo '<form action="'. URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=submit') . '" method="POST">';

		DrawHeader(
				'',
				SubmitButton( _( 'Cancel' ), 'submit[cancel]', '' ) . // No .primary button class.
				SubmitButton( _( 'SAVE' ), 'submit[save]' )
			);

		$createFromSelect = '<select id="fromTeacher" name="fromTeacher" style="width:400px;" onchange="myName(this.value,' . UserSYear() . ',' . UserSchool() .')">';
		$createFromSelect .= '<option value="NO" selected>   --- Select From Teacher ---   </option>';

		$createToSelect = '<select id="toTeacher" name="toTeacher" style="width:400px;>';
		$createToSelect .= '<option value="NO" selected>   --- Select To Teacher ---   </option>';

	
		foreach($teachers as $teacher){
			
			$selectValue = makeName($teacher['FIRST_NAME'],$teacher['MIDDLE_NAME'],$teacher['LAST_NAME']);

			$createFromSelect .= '<option value="' . 
							$teacher['STAFF_ID'] .'">'  . $selectValue . '</option>';

$createToSelect .= '<option value="' . 
							$teacher['STAFF_ID'] .'">'  . $selectValue . '</option>';
		}

			

		$createFromSelect .= '</select>';
		$createToSelect .= '</select>';


		$dataTable = '<div class="table-responsive"><table style=width:90%><tr><td style=width:60%>';

		$dataTable .=  $createFromSelect . "</td><td style=width:40%>" . $createToSelect . '</td></tr></table></div>';

		echo $dataTable;



		echo '<div id="teacherCourses"></div>';


		echo '</form>';
}else{ //your not an admin so go away.

	$info = 'This is an administrative function only</br></br>';

		$instructions = '<div class="table-responsive"><table style=width:80%;align:center><tr><td>' . $info . '</td></tr></table></div>';


		echo $instructions;

}





//either the word all for all teachers or the teacher ID for 1
function getTeacherInfo($list){
	if(strtoupper($list) == 'ALL'){
		$addWHERE = '';
	}else{
		$addWHERE = " AND STAFF_ID = '" . $list . "'";
	}

	$teachers = DBGET("SELECT * 
					FROM public.staff
					Where upper(Profile) = 'TEACHER'
					   AND SYEAR = " . UserSYear() .
					   $addWHERE );

	return $teachers;
}



//Combine fields to make a proper name that is in the database
function makeName($firstName,$middleName,$lastName){

	if($firstName != NULL){
		$fullName = $firstName . ' ';
	}
	if($middleName != NULL){
		$fullName .= $middleName . ' ';
	}
	if($lastName != NULL){
		$fullName .= $lastName . ' ';
	}

	return $fullName;

}

?>


<script type="text/javascript">
	function myName(val,schoolyear,schoolid) {
       
		

        //Make an Ajax request to a PHP script called car-models.php
        //This will return the data that we can add to our Select element.
        $.ajax({
            url: 'modules/Utilities/getTeacher.fnc.php',
            type: 'POST',
            //dataType: 'JSON',
            data:{info: val,
            	  year: schoolyear,
            	  schoolid: schoolid},	
            success: function(response){
            	var len = response.length;
                //Log the data to the console so that
                //you can get a better view of what the script is returning.
                //console.log(response);

               

                //Change the text of the default "loading" option.
              // $('#orderAMOUNT_' + myArr[2] ).val(myArr[3]);
               $('#teacherCourses').html(response);

             //  $('#complimentWarning' + myArr[2]).html('Additional Information Needed');

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




    }


</script>
