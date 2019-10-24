<?php
/*
--------------------------------------------------------------------------------
HHIMS - Hospital Health Information Management System
Copyright (c) 2011 Information and Communication Technology Agency of Sri Lanka
<http: www.hhims.org/>
----------------------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify it under the
terms of the GNU Affero General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR 
A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along 
with this program. If not, see <http://www.gnu.org/licenses/> or write to:
Free Software  HHIMS
ICT Agency,
160/24, Kirimandala Mawatha,
Colombo 05, Sri Lanka
---------------------------------------------------------------------------------- 
Author: Author: Mr. Jayanath Liyanage   jayanathl@icta.lk
                 
URL: http://www.govforge.icta.lk/gf/project/hhims/
----------------------------------------------------------------------------------
*/

	include("header.php");	///loads the html HEAD section (JS,CSS)
?>
<?php echo Modules::run('menu'); //runs the available menu option to that usergroup ?>
<div class="container" style="width:95%;">
	<div class="row" style="margin-top:55px;">
	
	<div class="col-md-10 " >
		<?php
			if (isset($PID)){
				echo Modules::run('patient/banner',$PID);
			}
		?>
		<div class="panel panel-default  "  style="padding:2px;margin-bottom:1px;" >
			<div class="panel-heading" ><b>
			<?php 
				echo $title; 
			?></b>
			</div>
		
			<div class="" style="margin-bottom:1px;padding-top:8px;">
				<?php 
					echo '<table class="table table-condensed table-hover" style="margin-bottom:0px;">';
						echo '<tr>';
							echo '<td>';
								echo 'Complaint / Injuries : <b id="opd_complaint">'.$opd_visits_info["Complaint"].'</b>';
							echo '</td>';
							echo '<td>';
								echo 'Onset Date : <b>'.$opd_visits_info["OnSetDate"].'</b>';
							echo '</td>';
							echo '<td>';
								//echo 'Visit type : <b>'.$opd_visits_info["visit_type_name"].'</b>';
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<td>';
								if (isset($opd_presciption_info["Status"])){
									echo 'Status : <b>'.$opd_presciption_info["Status"].'</b>';
								}
							echo '</td>';						
							echo '<td>';
								if (isset($opd_visits_info["Doctor"])){
									echo 'Doctor : <b>'.$opd_visits_info["Doctor"].'</b>';
								}
							echo '</td>';
							echo '<td>';							
								if (isset($opd_presciption_info["PrescribeDate"])){
									echo 'Prescribe Date : <b>'.$opd_presciption_info["PrescribeDate"].'</b>';
									
								}
							echo '</td>';
						echo '</tr>';
                                                echo '<tr>';
							echo '<td>';
								if ($opd_presciption_info["Status"]=='Dispensed'){
									echo 'Dispensed By : <b>'.$opd_presciption_info["LastUpDateUser"].'</b>';
								}
							echo '</td>';
		
						echo '<tr>';
							echo '<td>';
							echo '</td>';						
							echo '<td>';
								echo '<br><div>';
								echo '<a href = "'.site_url("pharmacy/cancel_call/".$opd_presciption_info["PRSID"]).'" title="You can cancel this call now. and call them latter" class="btn btn-danger "><span class="glyphicon glyphicon-pause"></span> Cancel this Call</a>';
                                                                //echo '<a href = "'.site_url("pharmacy/call/".$opd_presciption_info["PRSID"]).'" title="Recall to Patient" class="btn btn-warning btn-xs1"><span class="glyphicon glyphicon-volume-up"></span>Call Again</a>';
                                                                //echo '<button type="button" onclick="call_rec(\''.$opd_presciption_info["PRSID"]).'\')"   class="btn btn-warning btn-xs"><span class="glyphicon glyphicon-volume-up"></span> Call Again</button>';
                                                                echo '<a href = "'.site_url("pharmacy/dispense/".$opd_presciption_info["PRSID"]).'"class="btn btn-success "><span class="glyphicon glyphicon-play"></span> Proceed to dispensing</a>';
								echo '</div>';
							echo '</td>';
							echo '<td>';							
							echo '</td>';
						echo '</tr>';	
					echo '</table><br>';	
				?>				
			</div>
		</div>	
	</div>
	</div>
</div>