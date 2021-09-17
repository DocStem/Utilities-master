<?php



DrawHeader( ProgramTitle() );

if( User( 'PROFILE' ) === 'admin'){
		//echo('<pre>' . print_r($_REQUEST) . '</pre>');

		$info = 'This routine clones a current or past student schedule to current Active Student(s).</br> 
			The schedule shown for Clone Source Student will have both the original schedule Period names (if in the past) and the current Period names for identification purposes.</br></br>
			<b> Make a system database backup prior to running the utility in the advent you make a mistake </b></br>
			This module is not going to validate the number of students in the class or for conflicts of period.</br></br>
			Step 1 : Chose filters to reduce the number of possible students as clone source.</br>
			Step 2 : Click the class periods of the Clone Source or Select All</br>
			Step 3 : Filter for Target Clone Students</br>
			Step 4 : Click Target Students</br>
			Step 5 : Process Schedules.</br></br>';

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

 
		$createFromSources = '<select id="fromSources" name="fromSources" style="width:400px;" onchange="myName(this.value,' . UserSyear() . ',' . UserSchool(). ')">';
		$createFromSources .= '<option value="NO" selected>   --- Possible Students ---   </option>';
		
		$createFromSources .= '</select></br>';


//====================================================================

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

		

}else{ //your not an admin so go away.

	$info = 'This is an administrative function only</br></br>';

		$instructions = '<div class="table-responsive"><table style=width:80%;align:center><tr><td>' . $info . '</td></tr></table></div>';


		echo $instructions;

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
               $('#' + elem +'Sources').append(response);
           }else{
           	//alert(response);
           	$('#ToStudents').append(response);
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