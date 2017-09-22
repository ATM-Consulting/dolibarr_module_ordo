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
 *	\file       /ordo/scrum.php
 *	\ingroup    projet
 *	\brief      Project card
 */


	require('config.php');

	$TWorkstation = array(
        0=>array('nb_ressource'=>1, 'velocity'=>1, 'background'=>'#FFEBD9', 'name'=>'Non ordonnancé','id'=>0) // base de 7h par jour
    );

    if($conf->workstation->enabled) {
        define('INC_FROM_DOLIBARR',true);
        dol_include_once('/workstation/config.php');
        $ATMdb=new TPDOdb;
        $TWorkstation = TWorkstation::getWorstations($ATMdb,true,false,$TWorkstation, true);
    }
    else {
        setEventMessage($langs->trans("moduleWorkstationNeeded").' : <a href="https://github.com/ATM-Consulting/dolibarr_module_workstation" target="_blank">'.$langs->trans('DownloadModule').'</a>','errors');
    }

	$number_of_columns = 0 ;
	foreach($TWorkstation as $w_param) {
		$number_of_columns+=$w_param['nb_ressource'];
	}

    $hh =  GETPOST('hour_height');
    if(!empty($hh)) $_SESSION['hour_height'] = (int)$hh;

    $cw =  GETPOST('column_width');
    if(!empty($cw)) $_SESSION['column_width'] = (int)$cw;

	$tm = GETPOST('tilemode');
	if($tm!=='') $_SESSION['tile_mode'] = (int)$tm;

    $hour_height = empty($_SESSION['hour_height']) ? 50 : $_SESSION['hour_height'];
    $column_width = empty($_SESSION['column_width']) ? -1 : $_SESSION['column_width'];
    $tile_mode = !isset($_SESSION['tile_mode']) ? 1 : $_SESSION['tile_mode'];
	$day_height =  $hour_height * 7;

	$column_width = 200;//pour test

	llxHeader('', $langs->trans('GridTasks') , '','',0,0, array('/ordo/lib/konva/konva.js'));

	$form = new Form($db);

?>

						<div id="theGrid" style="border:1px dashed #999;background-color:#fff;"></div>
						<div id="refreshOrdo"><a href="<?php echo $_SERVER['PHP_SELF'] ?>"><?php echo $langs->trans('RefreshOrdo'); ?></a></div>

<style type="text/css">
#refreshOrdo {
	position:fixed;
	top:50px;
	right:0;
	padding:10px;
	background-color:green;
	display:none;
}
</style>

						<script type="text/javascript">
								var total_grid_with =<?php echo (int)($number_of_columns * $column_width); ?>;
								var TaskLayer;
								var canvasGrid;
								var CanvaWorkstation={};

						</script>

						<?php

						_draw_grid($TWorkstation, $column_width);

						if(empty($conf->global->SCRUM_HIDE_PROJECT_LIST_ON_THE_RIGHT)) {

						?>
						<div class="projects" style="float:left;">
						    <ul style="position:relative;width:200px; top:38px; overflow:visible;" id="list-projects" class="task-list needToResize" >

                            </ul>
						</div>

						<?php
						}
						?>

<?php

 _js_grid($TWorkstation, $day_height, $column_width);
function _order_by_name(&$a, &$b) {

    $r = strcmp($a['name'],$b['name']);
    if($r<0) return -1;
    elseif($r>0) return 1;
    else return 0;

}
function _js_grid(&$TWorkstation, $day_height, $column_width) {
    global $conf;

     $TWSVisible=array();
   	 if(!empty($_COOKIE['WSTogle'])) {
   	 	foreach($_COOKIE['WSTogle'] as $wsid=>$visible) {
   	 		$TWorkstation[$wsid]['visible'] = $visible;
   	 	}
   	 }

   	 $nb_ressource_total = 0;
    foreach($TWorkstation as &$ws) {
    	if(!isset($ws['visible']) || !empty($ws['visible'])) {
    		$nb_ressource_total+=(!empty($ws['nb_ressource']) ? $ws['nb_ressource'] : 1 );
    	}
    }

		?>
			<script type="text/javascript">
					var http = "<?php echo DOL_URL_ROOT; ?>";
		            var w_column = <?php echo $column_width; ?>;
		            var h_day = <?php echo $day_height; ?>;
		            var TDayOff = new Array( <?php echo $conf->global->TIMESHEET_DAYOFF; ?> );



		        </script>
		        <script type="text/javascript" src="./js/canvas.js.php"></script>
	            <script type="text/javascript" src="./js/makefixed.js"></script>
	            <script type="text/javascript" src="./js/svg.js"></script>

        	        <script type="text/javascript">
				var TVelocity = [];

				document.ordo = {};

				if(w_column == -1) {
					w_column = parseInt(($( window ).width() - $('#id-left').width() - 50) / <?php echo $nb_ressource_total + (empty($conf->global->SCRUM_HIDE_PROJECT_LIST_ON_THE_RIGHT) ? 2 : 0); ?>);
					$('div.columnordo').each(function(i,item) {
						$item = $(item);
						var nb_r = $item.attr('ws-nb-ressource');
						$item.css('width', w_column*nb_r);

						$item.find('ul').css('width', w_column*nb_r);
					});


				}
				$(document).ready(function(){
  					$('#ws-list-top').width($( window ).width());
					$('#theGrid').width( $('div.konvajs-content').width() );

				     document.ordo = new TOrdonnancement();

					 <?php
					 	foreach($TWorkstation as $w_id=>$w_param) {
					 	    ?>

					 		var w = new TWorkstation();
                            w.nb_ressource = <?php echo $w_param['nb_ressource']; ?>;
                            w.velocity = <?php echo $w_param['velocity']; ?>;
                            w.id = "<?php echo $w_id; ?>";

					 		document.ordo.addWorkstation(w);

					 		<?php
						}

                        if(!empty($_COOKIE['WSTogle'])) {
                            foreach($_COOKIE['WSTogle'] as $wsid=>$visible) {

                                if(empty($visible)) {
                                    ?>
                                    toggleWorkStation(<?php echo (int)$wsid; ?>);
                                    <?php

                                }

                            }

                        }


					 ?>

					document.ordo.init(w_column, h_day,0.08);

				});
				</script><?php

}

function _draw_grid(&$TWorkstation, $column_width) {

	$width_table = 0; $offsetX = 0;
	foreach($TWorkstation as $w_id=>&$w_param) {

		$back = empty($w_param['background']) ? '' : 'background:'.$w_param['background'].';';

		$w_column = $column_width*$w_param['nb_ressource'];

		$width_table+=$w_column;

		?>	<script type="text/javascript">

					CanvaWorkstation[<?php echo $w_id; ?>] = {};
					CanvaWorkstation[<?php echo $w_id; ?>].x = <?php echo $offsetX; ?>;
					CanvaWorkstation[<?php echo $w_id; ?>].width = <?php echo $w_column; ?>;

			</script>
		<?php

		$offsetX+=$w_column;

	}

}


	?>

		</div>

		<div style="display:none">

			<ul>
			<li id="task-blank">
				<header>|||</header>
				<div rel="content">
    				<span rel="project" style="display:none;"></span> <span rel="task-link">[<a href="#" rel="ref"> </a>] <span rel="label" class="classfortooltip" title="">label</span></span>
    				<div rel="divers"></div>
                    <div rel="time-projection" <?php echo empty($conf->global->SCRUM_SHOW_SHOW_ESTIMATED_START_END) ? 'style="display:none"': ''; ?>></div>
                    <div rel="time-rest"></div>
                    <div rel="users"></div>
    				<div rel="time-end"></div>
    				<a href="javascript:;" class="button split" title="<?php echo $langs->trans('SplitTask'); ?>">x</a>
				</div>
				<div class="loading"></div>
			</li>
			</ul>

		</div>

<?php

	llxFooter();
