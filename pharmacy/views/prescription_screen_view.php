<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
	echo Modules::run('menu'); //runs the available menu option to that usergroup
?>
	<div class="container" style="width:95%;">
		<div class="row" style="margin-top: 55px; padding-bottom: 10px; padding-top: 15px;">
            <table border="0" width="100%" >
                    <tr >
                        <td valign="top" class="leftmaintable">
		<?php echo Modules::run('leftmenu/pharmacy'); //runs the available left menu for preferance ?>
	                          </td>
                        <td valign="top" class="rightmaintable">
			<div class="panel panel-default"  >
				<div class="panel-heading"><b>Prescription list</b></div>
                <div class="modal fade" id="daily" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                <div class="modal fade" id="order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                <div class="modal fade" id="prescription" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                <div class="modal fade" id="prescription-by-drug" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                <div class="modal fade" id="current-stock" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
				<div id="patient_list">
				<?php 
                    echo'<table border=0 class="table table-condensed table-hover" width="100%"><tr>';
                    echo '<td>';
                    echo '<b>&nbsp;&nbsp;Date</b>';
                    echo '</td>';
                    echo '<td>';
                    echo '<b>&nbsp;&nbsp;' . date('Y-m-d'). '</b>';
                    echo '</td>';
                    echo '<td>';
                    echo '<b>&nbsp;&nbsp;PatientID</b>';
                    echo '</td>';
                    echo '<td>';
                    echo '<input type="text" class="input" id = "PID" name="PID" autofocus  value=""/>';
                    echo '</td>';
                     echo '<td>';
                    echo '<b>&nbsp;&nbsp;PrescriptionID</b>';
                    echo '</td>';
                    echo '<td>';

                    echo '<input type="text" class="input" ID = "PRSID" name="PRSID"value=""/>';
                    echo '</td>';
                    echo '<tr>';
                    echo '<td>';
                    echo '<b>&nbsp;&nbsp;Status</b>';
                    echo '</td>';
                    echo '<td style="text-align:left">';
                    echo '<input type="checkbox" class="" checked=checked disabled name="pending"/>Pending';
                    echo '</td>';
                    echo '<td style="text-align:left">';
                    echo '<input type="checkbox" class="" name="serving" id="serving"/>Serving';
                    echo '</td>';
                    echo '<td style="text-align:left">';
                    
                    echo '<input type="button" class="btn btn-xs btn-primary" value="Search" default onclick="go_search()" />';
                    echo '</td>';
                    echo '<td style="text-align:left">';                    
                    echo '</td>';
                    echo '<td style="text-align:left">';                    
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';
                    echo '<div id="data_div"></div>';
                ?>
				</div>
			</div>
                        </td>
                      </tr>
                      </table>        
		</div>
	</div>
</div>
<script>
    $(function(){
        $("#PID").keypress(
            function(e) {
                if(e.which == 13) {
                go_search();
            }
        });
        $("#PRSID").keypress(
            function(e) {
                if(e.which == 13) {
                go_search('','','');
            }
        });
       get_data();
    });
    function get_data(pid,prsid,is_serving){
           var request = $.ajax({
                url: "<?php echo base_url(); ?>index.php/pharmacy/ajax_prescription_data",
                type: "post",
                data:{"pid":pid,"prsid":prsid,"is_serving":is_serving}
            });
            request.done(function (response, textStatus, jqXHR){
                display_data(JSON.parse(response));
            });
    }
    
    function display_data(data){
            //console.log(data);
            var html = '';
            html += '<table border=1 class="table table-condensed table-hover" width="100%">';
            html += '<tr>';
            html += '<th>HIN</th><th>PID</th><th>Patient</th><th>Prescription</th><th>Date</th><th>Status</th>';
            html += '</tr>';
            if (data){
                for (var i in data){
                    html += '<tr style="cursor:pointer" >';//onclick="open_rec(\''+data[i]["PRSID"]+'\')"
                    html += '<th>' + data[i]["HIN"] + '</th><th>' + data[i]["PID"] + '</th><td>' + data[i]["Full_Name_Registered"] + ' ' + data[i]["Personal_Used_Name"] + '</td><th>' + data[i]["PRSID"] + '</th><th>' + data[i]["CreateDate"] + '</th><th>' + data[i]["Status"] + '</th>';
					//html += '<th>' + data[i]["is_called"] + '</th>';
					if (data[i]["is_called"] == 1){
						html += '<td><button type="button" onclick="call_rec(\''+data[i]["PRSID"]+'\')"   class="btn btn-warning btn-xs"><span class="glyphicon glyphicon-volume-up"></span> Call Again</button></td>';
					}
					else{
						html += '<td><button type="button" onclick="call_rec(\''+data[i]["PRSID"]+'\')"   class="btn btn-success btn-xs"><span class="glyphicon glyphicon-volume-up"></span> Call</button></td>';
					}
                    html += '</tr>';
                }
            }
            if (data.length == 0){
             html += '<tr>';
            html += '<th> - No Prescription available - </th>';
            html += '</tr>';
            }
            
            html += '</table>';
            $("#data_div").html(html);
    }
    
    function go_search(){
        get_data($("#PID").val(),$("#PRSID").val(),$("#serving").attr("checked"));
    }
    
    function open_rec(rec){
        self.document.location="<?php echo base_url(); ?>"+"index.php/pharmacy/dispense/"+rec;
    }
	
	function call_rec(rec){
        self.document.location="<?php echo base_url(); ?>"+"index.php/pharmacy/call/"+rec;
    }
    

</script>