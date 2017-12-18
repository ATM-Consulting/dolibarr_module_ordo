<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007-2014 ATM Consulting <contact@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       dev/skeletons/skeleton_page.php
 *		\ingroup    mymodule othermodule1 othermodule2
 *		\brief      This file is an example of a php page
 *		\version    $Id: skeleton_page.php,v 1.19 2011/07/31 22:21:57 eldy Exp $
 *		\author		Put author name here
 *		\remarks	Put here some comments
 */
// Change this following line to use the correct relative path (../, ../../, etc)
require '../config.php';
// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
dol_include_once('/core/lib/admin.lib.php');
dol_include_once('/core/class/extrafields.class.php');

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
	{
		if ($code == 'SCRUM_USE_PROJECT_PRIORITY')
		{
			$extrafields=new ExtraFields($db);
            $default_value = array('options'=> array(0=>$langs->trans('Normal'), 1=>$langs->trans('Important')));
            $res = $extrafields->addExtraField('priority', 'Priorité', 'select', 1, 0, 'projet', false, false, '', serialize( $default_value ) );
		}
		else if($code == 'SCRUM_GROUP_TASK_BY_RAL' && GETPOST($code) == 1) {
			$extrafields=new ExtraFields($db);
		    $res = $extrafields->addExtraField('fk_product_ral', 'RALLinkedToOrder', 'varchar', '', 255, 'projet_task');
		}
		/*else if($name == 'SCRUM_GROUP_TASK_BY_PRODUCT' && $param == 1) {
            $extrafields=new ExtraFields($db);
            $res = $extrafields->addExtraField('grou', 'Priorité', 'select', 1, 0, 'projet', false, false, '', serialize( $default_value ) );
        }*/
		else if($code == 'SCRUM_DEFAULT_HOUR_HEIGHT') $_SESSION['hour_height'] = GETPOST($code);

        setEventMessage( $langs->trans('RegisterSuccess') );
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		setEventMessage( $langs->trans('RegisterSuccess') );
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

llxHeader('','Gestion de scrumboard, à propos','');

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre('Ordo',$linkback,'setup');

showParameters();

function showParameters() {
	global $db,$conf,$langs,$bc;

	$html=new Form($db);

	$var=false;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Parameters").'</td>'."\n";
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="100">'.$langs->trans("Value").'</td>'."\n";

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("NumberOfWorkingHourInDay").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_TIMESHEET_WORKING_HOUR_PER_DAY">';
	print '<input type="text" name="TIMESHEET_WORKING_HOUR_PER_DAY" value="'.$conf->global->TIMESHEET_WORKING_HOUR_PER_DAY.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("UseProjectPriority").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_USE_PROJECT_PRIORITY">';
	print $html->selectyesno("SCRUM_USE_PROJECT_PRIORITY",$conf->global->SCRUM_USE_PROJECT_PRIORITY,1);
	print '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("GroupTaskByProduct").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_GROUP_TASK_BY_PRODUCT');
	print '</td></tr>';


	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("GroupTaskByRAL").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_GROUP_TASK_BY_RAL">';
	print $html->selectyesno("SCRUM_GROUP_TASK_BY_RAL",$conf->global->SCRUM_GROUP_TASK_BY_RAL,1);
	print '&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("GroupTaskByCustomer").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_GROUP_TASK_BY_CUSTOMER');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("ProductTolerance").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_GROUP_TASK_BY_PRODUCT_TOLERANCE">';
	print '<input type="text" name="SCRUM_GROUP_TASK_BY_PRODUCT_TOLERANCE" value="'.$conf->global->SCRUM_GROUP_TASK_BY_PRODUCT_TOLERANCE.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("TimeMoreForPrevision").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_TIME_MORE_PREVISION">';
	print '<input type="text" name="SCRUM_TIME_MORE_PREVISION" value="'.$conf->global->SCRUM_TIME_MORE_PREVISION.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("TimeMoreForPrevisionPropal").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_TIME_MORE_PREVISION_PROPAL">';
	print '<input type="text" name="SCRUM_TIME_MORE_PREVISION_PROPAL" value="'.$conf->global->SCRUM_TIME_MORE_PREVISION_PROPAL.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("WhenBeginOrdo").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_TIME_ORDO_START">';
	print '<input type="text" name="SCRUM_TIME_ORDO_START" value="'.$conf->global->SCRUM_TIME_ORDO_START.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("WhenEndOrdo").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_TIME_ORDO_END">';
	print '<input type="text" name="SCRUM_TIME_ORDO_END" value="'.$conf->global->SCRUM_TIME_ORDO_END.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("heightOfTaskIsDividedByRessource").'</td>&nbsp;';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_HEIGHT_DIVIDED_BY_RESSOURCE');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("hideProjectsOnTheRight").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_HIDE_PROJECT_LIST_ON_THE_RIGHT');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("showLinkedContactToTask").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_SHOW_LINKED_CONTACT');
	print '</td></tr>';

	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("showTaskWithoutDuration").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_SHOW_TASK_WITHOUT_DURATION');
	print '</td></tr>';

	$TSnapMode=array(''=>$langs->trans('None'), 'SAME_PROJECT_AFTER'=>$langs->trans('SnapTaskFromSameProjectAfter'));
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("SnapMode").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_SNAP_MODE">';
	print $html->selectarray('SCRUM_SNAP_MODE', $TSnapMode, $conf->global->SCRUM_SNAP_MODE);
    print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("ORDO_ELASTIC_TASK").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('ORDO_ELASTIC_TASK');
	print '</td></tr>';
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("SCRUM_SHOW_SHOW_ESTIMATED_START_END").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_SHOW_SHOW_ESTIMATED_START_END');
	print '</td></tr>';
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("fieldsToHide").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_TFIELD_TO_HIDE_ON_TASK_HOVER">';
	print '<input type="text" name="SCRUM_TFIELD_TO_HIDE_ON_TASK_HOVER" value="'.$conf->global->SCRUM_TFIELD_TO_HIDE_ON_TASK_HOVER.'" />';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("hideUsers").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print ajax_constantonoff('SCRUM_HIDE_USERS_ON_TASK_HOVER');
	print '</form>';
	
	$var=!$var;
	print '<tr '.$bc[$var].'>';
	print '<td>'.$langs->trans("defaultHeight").'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
	print '<td align="right" width="300">';
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="set_SCRUM_DEFAULT_HOUR_HEIGHT">';
	print '<input type="text" name="SCRUM_DEFAULT_HOUR_HEIGHT" value="'.$conf->global->SCRUM_DEFAULT_HOUR_HEIGHT.'" size="3" />&nbsp;';
	print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
	print '</form>';
	print '</td></tr>';
	
	print '</table>';
}
?>
<br /><br />
<table width="100%" class="noborder">
	<tr class="liste_titre">
		<td>A propos</td>
		<td align="center">&nbsp;</td>
		</tr>
		<tr class="impair">
			<td valign="top">Module développé par </td>
			<td align="center">
				<a href="http://www.atm-consulting.fr/" target="_blank">ATM Consulting</a>
			</td>
		</td>
	</tr>
</table>
<?php

// Put here content of your page
// ...

/***************************************************
* LINKED OBJECT BLOCK
*
* Put here code to view linked object
****************************************************/
//$somethingshown=$asset->showLinkedObjectBlock();

// End of page
$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
