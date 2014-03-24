<?php
/* Copyright (C) 2014 Alexis Algoud        <support@atm-conuslting.fr>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 *	\file       /scrumboard/scrum.php
 *	\ingroup    projet
 *	\brief      Project card
 */

 
	require('config.php');

	llxHeader('', $langs->trans('Tasks') , '','',0,0, array('/scrumboard/script/scrum.js.php'));
	
	$id_projet = (int)GETPOST('id');

	$object = new Project($db);
	$object->fetch($id_projet);
	if ($object->societe->id > 0)  $result=$object->societe->fetch($object->societe->id);

	$head=project_prepare_head($object);
    dol_fiche_head($head, 'scrumboard', $langs->trans("Scrumboard"),0,($object->public?'projectpub':'project'));

	$form = new Form($db);

	/*
		 *   Projet synthese pour rappel
		 */
		print '<table class="border" width="100%">';

		$linkback = '<a href="'.DOL_URL_ROOT.'/projet/liste.php">'.$langs->trans("BackToList").'</a>';

		// Ref
		print '<tr><td width="30%">'.$langs->trans('Ref').'</td><td colspan="3">';
		// Define a complementary filter for search of next/prev ref.
        if (! $user->rights->projet->all->lire)
        {
            $objectsListId = $object->getProjectsAuthorizedForUser($user,$mine,0);
            $object->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
        }
		print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
		print '</td></tr>';

		// Label
		print '<tr><td>'.$langs->trans("Label").'</td><td>'.$object->title.'</td></tr>';

		// Customer
		print "<tr><td>".$langs->trans("Company")."</td>";
		print '<td colspan="3">';
		if ($object->societe->id > 0) print $object->societe->getNomUrl(1);
		else print '&nbsp;';
		print '</td></tr>';

		// Visibility
		print '<tr><td>'.$langs->trans("Visibility").'</td><td>';
		if ($object->public) print $langs->trans('SharedProject');
		else print $langs->trans('PrivateProject');
		print '</td></tr>';

		// Statut
		print '<tr><td>'.$langs->trans("Status").'</td><td>'.$object->getLibStatut(4).'</td></tr>';
	
		// Statut
		print '<tr><td>'.$langs->trans("CurrentVelocity").'</td><td>'.convertSecondToTime(scrum_getVelocity($object)).$langs->trans('HoursPerDay').'</td></tr>';

		print "</table>";
?>
<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/scrumboard/css/scrum.css',1) ?>">

		<div class="content">
	
			<table id="scrum" id_projet="<?php echo $id_projet ?>">
				<tr>
					<!-- <td><?php echo $langs->trans('Ideas'); ?></td></td> -->
					<td><?php echo $langs->trans('toDo'); ?>		</td></td>
					<td><?php echo $langs->trans('inProgress'); ?></td></td>
					<td><?php echo $langs->trans('finish'); ?></td></td>
				</tr>
				<tr>
					<!-- <td class="projectDrag droppable" id="task-idea" rel="idea">
						<ul id="list-task-idea" class="task-list" rel="idea">
						
						</ul>
					</td> -->
					<td class="projectDrag droppable" id="task-todo" rel="todo">
						<ul id="list-task-todo" class="task-list" rel="todo">
						
						</ul>
					</td>
					<td class="projectDrag droppable" id="task-inprogress" rel="inprogress">
						<ul id="list-task-inprogress" class="task-list" rel="inprogress">
						
						</ul>
					</td>
					<td class="projectDrag droppable" id="task-finish" rel="finish">
						<ul id="list-task-finish" class="task-list" rel="finish">
						
						</ul>
					</td>
				</tr>
			</table>
<?php	
	/*
	 * Actions
	*/
	print '<div class="tabsAction">';

	if ($user->rights->projet->all->creer || $user->rights->projet->creer)
	{
		if ($object->public || $object->restrictedProjectArea($user,'write') > 0)
		{
			print '<a class="butAction" href="javascript:create_task('.$object->id.');">'.$langs->trans('AddTask').'</a>';
		}
		else
		{
			print '<a class="butActionRefused" href="#" title="'.$langs->trans("NotOwnerOfProject").'">'.$langs->trans('AddTask').'</a>';
		}
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.$langs->trans("NoPermission").'">'.$langs->trans('AddTask').'</a>';
	}

	print '</div>';
?>
		
		</div>
		
		<div style="display:none">
			
			<ul>
			<li id="task-blank">
				<div class="progressbaruser"></div>
				<div class="progressbar"></div>
				<div class="actions">
				<select rel="progress">
					<?php
					for($i=5; $i<=95;$i+=5) {
						?><option value="<?php echo $i ?>"><?php echo $i ?>%</option><?php
					}
					?>
				</select>
				<span rel="time"></span>
				</div>
				
				<?php echo img_picto('', 'object_scrumboard@scrumboard') ?> [<a href="#" rel="ref"> </a>] <span rel="label" class="classfortooltip" title="">label</span> 
			</li>
			</ul>
			
		</div>
		
		
		<div id="saisie" style="display:none;"></div>
		
		<script type="text/javascript">
			$(document).ready(function() {
				loadTasks(<?php echo $id_projet ?>);
				project_init_change_type(<?php echo $id_projet ?>);
			});
		</script>
		
<?php

	llxFooter();
