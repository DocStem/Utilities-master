<?php
/**
 * Utilities module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 * 
 * @package RosarioSIS
 * @subpackage modules
 */

$module_name = dgettext( 'Utilities', 'Utilities' );

if ( $RosarioModules['Utilities'] )
{
    

    //$menu['Utilities']['admin'] = 'Advanced';

    $menu['Utilities']['admin'] = array(
        'title' => dgettext( 'Utilities','Utilities' ),
        'default' => 'Utilities/Teacherchange.php', // Program loaded by default when menu opened.
        'Utilities/Teacherchange.php' => dgettext('TeacherChange','Teacher Change'),
        'Utilities/scheduleCloner.php' => dgettext('CloneStudentSchedule','Clone Student Schedule'),
        );


}