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
        'title' => _( 'Utilities' ),
        'Utilities/Teacherchange.php' => _( 'Teacher Change' ),
        );


}