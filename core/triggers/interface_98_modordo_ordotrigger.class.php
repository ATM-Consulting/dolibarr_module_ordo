<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2013 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		core/triggers/interface_99_modMyodule_Mytrigger.class.php
 * 	\ingroup	scrumboard
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_99_modMymodule_Mytrigger.class.php
 * 					interface_99_all_Mytrigger.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceMytrigger
 * 				- The constructor method must be named InterfaceMytrigger
 * 				- The name property name must be Mytrigger
 */

/**
 * Trigger class
 */
class Interfaceordotrigger
{

    private $db;

    /**
     * Constructor
     *
     * 	@param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'ordo@ordo';
    }

    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') {
            return $langs->trans("Development");
        } elseif ($this->version == 'experimental')

                return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else {
            return $langs->trans("Unknown");
        }
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action
        // Users
        
      
       if ($action === 'PROJECT_CREATE') {
       	
			if(empty($object->array_options['options_color'])) {
				
				dol_include_once('/ordo/lib/ordo.lib.php');
				
				$object->array_options['options_color'] = '#'.scrumboard_random_color();
				$object->update($user, 1);
			}
		
		
            dol_syslog(
                "Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
            );
       } 
       else if($action === 'TASK_CREATE') {
            
            if(!empty($conf->global->SCRUM_ADD_TASKS_TO_GRID)) {
                  $object->array_options['options_grid_use'] = 1;  
                  $object->update($user,1);
	          	  $object->insertExtraFields();
            }
            
            dol_syslog(
                "Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
            );
       }
	   else if($action === 'ACTION_CREATE' || $action =='ACTION_MODIFY') {
	   	//TODO je ne sais pas si encore utile TO CHECK
			$fk_task = 0;
			
			$object->fetchObjectLinked();
			if(!empty($object->linkedObjectsIds['task'])) {
				$row = each($object->linkedObjectsIds['task']);
				$fk_task = $row[1];	
			}
			
			$fk_project_task = GETPOST('fk_project_task'); 
			
			if(!empty($fk_project_task)) {
				
				list($fk_project, $fk_task) = explode('_',$fk_project_task);
				
				if(!empty($fk_task)) {
					if(!empty($object->linkedObjectsIds['task'])) {
						$object->updateObjectLinked( $fk_task , 'task');
					}
					else{
						$object->add_object_linked( 'task' , $fk_task );	
					}	
					
				}
				
			}
			/*var_dump($object);
			exit('!');
	*/
			
		
	   }
       
        return 0;
    }
}
