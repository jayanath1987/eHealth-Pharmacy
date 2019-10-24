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
with this program. If not, see <http://www.gnu.org/licenses/> 




---------------------------------------------------------------------------------- 
Date : June 2016
Author: Mr. Jayanath Liyanage   jayanathl@icta.lk

Programme Manager: Shriyananda Rathnayake
URL: http://www.govforge.icta.lk/gf/project/hhims/
----------------------------------------------------------------------------------
*/
class Pharmacy extends MX_Controller {
	 function __construct(){
		parent::__construct();
		$this->checkLogin();
		$this->load->library('session');
		if(isset($_GET["mid"])){
			$this->session->set_userdata('mid', $_GET["mid"]);
		}			
	 }

	public function index()
	{
            if($this->session->userdata('WT') == "1" ){
                $this->opd_presciption_search();
            }else{
                $this->clinic_presciption_search();
            }
		//$this->load->view('patient');
		//$this->opd_presciption_search();
	}

	public function show_list($type){
		if ($type == "OPD"){
			$this->opd_presciption_search($type);
		}
		else if($type == "CLN"){
			$this->clinic_presciption_search($type);
		}
	}
	public function opd_presciption_search($type=null){
      $qry = "SELECT              
	  patient.HIN as HIN, 
	  opd_presciption.PRSID,
	  opd_presciption.Dept,
	  CONCAT(patient.Full_Name_Registered,' ', patient.Personal_Used_Name), 
	  opd_presciption.CreateDate as PrescribeDate, 
	  opd_presciption.Status          
	  from opd_presciption 
	  LEFT JOIN `patient` ON patient.PID = opd_presciption.PID 
	  where (opd_presciption.Status != 'Draft') 
	  
			";
	if ($type){
		$qry .= "and opd_presciption.Dept = '$type' ORDER BY (opd_presciption.CreateDate)";
	}
        $this->load->model('mpager',"visit_page");
		
        $visit_page = $this->visit_page;
        $visit_page->setSql($qry);
        $visit_page->setDivId("patient_list"); //important
        $visit_page->setDivClass('');
        $visit_page->setRowid('PRSID');
        $visit_page->setCaption("OPD Prescription list");
        $visit_page->setShowHeaderRow(true);
        $visit_page->setShowFilterRow(true);
        $visit_page->setShowPager(true);
        $visit_page->setColNames(array("HIN","ID","Dept", "Patient", "Date","Status"));
        $visit_page->setRowNum(25);
        $visit_page->setColOption("HIN", array("search" => true, "hidden" => FALSE,"width"=>"80px"));
        $visit_page->setColOption("PRSID", array("search" => false, "hidden" => true,"width"=>"30px"));
	$visit_page->setColOption("Dept", array("search" => true, "hidden" => false,"width"=>"50px"));
        //$visit_page->setColOption("Patient", array("search" => true, "hidden" => false));
        $visit_page->setColOption("PrescribeDate", array("search" => TRUE, "hidden" => false ));
        $visit_page->setColOption("PrescribeDate", $visit_page->getDateSelector(date("Y-m-d")));
        $visit_page->setColOption(
            "Status", array("stype"         => "select",
                                           "searchoptions" => array("value"        => ":All;Dispensed:Dispensed;Pending:Pending;Serving:Serving","defaultValue" => "Pending"))
        );
        //$visit_page->setColOption("Status", array("search" => false, "hidden" => false));
        //$visit_page->setColOption("PID", array("search" => true, "hidden" => true));
        $visit_page->gridComplete_JS
            = "function() {
        $('#patient_list .jqgrow').mouseover(function(e) {
            var rowId = $(this).attr('id');
            $(this).css({'cursor':'pointer'});
        }).mouseout(function(e){
        }).click(function(e){
            var rowId = $(this).attr('id');
            window.location='".site_url("/pharmacy/dispense/")."/'+rowId;
        });
        }";
        $visit_page->setOrientation_EL("L");
		$data['pager'] = $visit_page->render(false);
		$this->load->vars($data);
        $this->load->view('search/prescription_search');	
	}	
        
        	public function call($prisid){
		if(!isset($prisid) ||(!is_numeric($prisid) )){
			$data["error"] = "Prescription  not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');	
			return;
		}
		$this->load->model('mpersistent');
		$this->load->helper('string');
        
		$data["opd_presciption_info"] = $this->mpersistent->open_id($prisid, "opd_presciption", "PRSID");
		if (empty($data["opd_presciption_info"])){
			$data["error"] ="Prescription not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');
			return;
		}
		
        if ($data["opd_presciption_info"]["Status"]=="Pending"){
            //marking the precription as serving
            $this->mpersistent->update("opd_presciption","PRSID",$prisid,array("Status"=>"Serving","served_by"=>$this->session->userdata('UID'),"is_called"=>1));
        }

		if ($data["opd_presciption_info"]["Dept"] == "OPD"){
			$this->load->model('mopd');
			if (isset($data["opd_presciption_info"]["OPDID"])){
				$data["opd_visits_info"] = $this->mopd->get_info($data["opd_presciption_info"]["OPDID"]);
			}
			if (empty($data["opd_visits_info"])){
				$data["error"] ="OPD not found";
				$this->load->vars($data);
				$this->load->view('pharmacy_error');
				return;
			}
		}
		$data["title"] = "Calling patient";
		$data["PID"] = $data["opd_visits_info"]["PID"];
		$this->load->vars($data);
        $this->load->view('call_view');	
	}
        
        	public function cancel_call($prsid){
		if(!isset($prsid) ||(!is_numeric($prsid) )){
			$data["error"] = "Prescription  not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');	
			return;
		}
		$this->load->model('mpersistent');
		$this->mpersistent->update("opd_presciption","PRSID",$prsid,array("Status"=>"Pending","served_by"=>null));
		$this->session->set_flashdata(
			'msg', 'REC: ' . 'Cancelled'
		);
		header("Status: 200");
		header("Location: ".site_url('pharmacy'));
		return;
	}
        
        
        
	public function clinic_presciption_search($type=null){
      $qry = "SELECT 
	  patient.HIN as HIN, 
	  clinic_prescription.clinic_prescription_id,
	  clinic_prescription.Dept,
	  CONCAT(patient.Full_Name_Registered,' ', patient.Personal_Used_Name) , 
	  clinic_prescription.CreateDate, 
	  clinic_prescription.Status 
	  from clinic_prescription 
	  LEFT JOIN `patient` ON patient.PID = clinic_prescription.PID 
	  where (clinic_prescription.Status <> 'Draft')
	  
			";
	if ($type){
		$qry .= "and clinic_prescription.Dept = '$type'";
	}
        $this->load->model('mpager',"visit_page");
		
        $visit_page = $this->visit_page;
        $visit_page->setSql($qry);
        $visit_page->setDivId("patient_list"); //important
        $visit_page->setDivClass('');
        $visit_page->setRowid('clinic_prescription_id');
        $visit_page->setCaption("Clinic Prescription list");
        $visit_page->setShowHeaderRow(true);
        $visit_page->setShowFilterRow(true);
        $visit_page->setShowPager(true);
        $visit_page->setColNames(array("HIN","ID","Dept", "Patient", "Date","Status"));
        $visit_page->setRowNum(25);
        $visit_page->setColOption("HIN", array("search" => true, "hidden" => FALSE,"width"=>"80px"));
        $visit_page->setColOption("clinic_prescription_id", array("search" => false, "hidden" => false,"width"=>"30px"));
	$visit_page->setColOption("Dept", array("search" => true, "hidden" => false,"width"=>"50px"));
        //$visit_page->setColOption("patient_name", array("search" => true, "hidden" => false));
        $visit_page->setColOption("CreateDate", array("search" => false, "hidden" => false ));
        $visit_page->setColOption("CreateDate", $visit_page->getDateSelector(date("Y-m-d")));
        $visit_page->setColOption(
            "Status", array("stype"         => "select",
                                           "searchoptions" => array("value"        => ":All;Dispensed:Dispensed;Pending:Pending;Serving:Serving","defaultValue" => "Pending"))
        );
        $visit_page->gridComplete_JS
            = "function() {
        $('#patient_list .jqgrow').mouseover(function(e) {
            var rowId = $(this).attr('id');
            $(this).css({'cursor':'pointer'});
        }).mouseout(function(e){
        }).click(function(e){
            var rowId = $(this).attr('id');
            window.location='".site_url("/pharmacy/clinic_dispense")."/'+rowId;
        });
        }";
        $visit_page->setOrientation_EL("L");
		$data['pager'] = $visit_page->render(false);
		$this->load->vars($data);
        $this->load->view('search/prescription_search');	
        ////
       
        
	}		
	public function save_dispense(){
		if($_POST){
			$PRSID = null;
			$drug_stock_id = null;
			$this->load->model('mpersistent');
			$this->load->model('mdrug_stock');
			foreach ($_POST as $k => $v) {
				if ($k == "PRSID"){
					$PRSID = $v;		
				}
				elseif ($k == "drug_stock_id"){
					$drug_stock_id = $v;
				}
				else{
					if ($k[0]!="_"){
						if(isset($_POST["_"+$k])){
							$drug_id = $_POST["_$k"];
						}
						else{
							$drug_id  = null;
						}
						$save_data = array(
							"Quantity" => $v,
							"Status" => "Dispensed"
						);
					
						//update($table=null,$key_field=null,$id=null,$data)
						$r = $this->mpersistent->update("prescribe_items","PRS_ITEM_ID",$k,$save_data);
						if ($r){
							$this->mdrug_stock->deduct_drug($drug_stock_id, $drug_id , $v);
						}
					}
				}
			}
			$save_data = array(
				"Status" => "Dispensed"
			);
			$this->mpersistent->update("opd_presciption","PRSID",$PRSID,$save_data);
			$this->session->set_flashdata(
				'msg', 'REC: ' . 'Dispensed'
			);
			//$this->dispense($PRSID);
                        $new_page   =   site_url('/pharmacy');
			header("Status: 200");
			header("Location: ".$new_page);
		}
	}
	public function dispense($prisid){
		if(!isset($prisid) ||(!is_numeric($prisid) )){
			$data["error"] = "Prescription  not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');	
			return;
		}
		$this->load->model('mpersistent');
		$this->load->helper('string');
		$data["opd_presciption_info"] = $this->mpersistent->open_id($prisid, "opd_presciption", "PRSID");
		if (empty($data["opd_presciption_info"])){
			$data["error"] ="Prescription not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');
			return;
		}
		
		$data['title'] = 'Prescription dispensing';
		if ($data["opd_presciption_info"]["Dept"] == "OPD"){
			$this->load->model('mopd');
			if (isset($data["opd_presciption_info"]["OPDID"])){
				$data["opd_visits_info"] = $this->mopd->get_info($data["opd_presciption_info"]["OPDID"]);
			}
			if (empty($data["opd_visits_info"])){
				$data["error"] ="OPD not found";
				$this->load->vars($data);
				$this->load->view('pharmacy_error');
				return;
			}
                        $UID=$this->session->userdata('UID');
                        $data["stock_info"] = $this->mopd->get_pharm_stock($UID);
                        if($data["stock_info"]==NULL){
			$data["stock_info"] = $this->mopd->get_stock_info($data["opd_visits_info"]["VisitType"]);
                        }
			$data["prescribe_items_list"] =$this->mopd->get_prescribe_items($prisid);
			if(isset($data["prescribe_items_list"])){
				for ($i=0;$i<count($data["prescribe_items_list"]); ++$i){
					if ($data["prescribe_items_list"][$i]["drug_list"] == "who_drug"){
						$drug_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["DRGID"], "who_drug", "wd_id");
if($data["prescribe_items_list"][$i]["Dosage"]!='')$dose_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["Dosage"], "drugs_dosage", "Dosage");
if($data["prescribe_items_list"][$i]["Frequency"]!='')$freq_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["Frequency"], "drugs_frequency", "Frequency");
if($data["prescribe_items_list"][$i]["HowLong"]!='')$period_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["HowLong"], "drugs_period", "Period");
						$drug_count=$this->mopd->get_drug_count($data["stock_info"]["drug_stock_id"],$data["prescribe_items_list"][$i]["DRGID"]);
					}	
					$data["prescribe_items_list"][$i]["drug_name"] = $drug_info["name"];
					$data["prescribe_items_list"][$i]["drug_dose"] = $drug_info["dose"];
					$data["prescribe_items_list"][$i]["drug_formulation"] = $drug_info["formulation"];
if(isset($dose_info["Factor"])) $data["prescribe_items_list"][$i]["dose_factor"]= $dose_info["Factor"];
if(isset($freq_info["Factor"])) $data["prescribe_items_list"][$i]["freq_factor"]= $freq_info["Factor"];
if(isset($period_info["Factor"])) $data["prescribe_items_list"][$i]["period_factor"]= $period_info["Factor"];
                    			$data["prescribe_items_list"][$i]["drug_count"] = $drug_count;
				}
			}
			$data['title'] = 'OPD Prescription dispensing';
		}
		$data['PID'] = $data["opd_presciption_info"]["PID"];
                $this->load->model('mpatient');
                $data["patient_allergy_list"] = $this->mpatient->get_allergy_list($data["opd_presciption_info"]["PID"]);
                $data["patient_current_lab_list"] = $this->mpatient->patient_current_lab_list($data["opd_presciption_info"]["PID"]);
                $data["patient_current_procedure_list"] = $this->mpatient->patient_current_procedure_list($data["opd_presciption_info"]["OPDID"]);
                $data["patient_current_injection_list"] = $this->mpatient->patient_current_injection_list($data["opd_presciption_info"]["OPDID"]);
                $data["patient_other_prescription"] = $this->mpatient->patient_other_prescription($data["opd_presciption_info"]["OPDID"],$data["opd_presciption_info"]["PRSID"]);
		$this->load->vars($data);
                $this->load->view('drug_dispense');	
	}
        
        	function cancel_prescription($prsid=null,$episode=null,$episode_id=null){
		$data = array();
		if (!$prsid){
			$data["error"] = "Prescription  not found";
		}
		if (!$episode){
			$data["error"] = "Prescription  not found";
		}
		if (!$episode_id){
			$data["error"] = "Prescription  not found";
		}
		$sve_data = array(
			"Status"=>"Cancelled"
		);
		$data["prsid"]  = $prsid;
		$data["episode"]  = $episode;
		$data["episode_id"]  = $episode_id;
		$this->load->model('mpersistent');
		$r = $this->mpersistent->update("opd_presciption","PRSID",$prsid,$sve_data);
		$data["sts"]  = $r;
		$this->load->vars($data);
        $this->load->view('cancel_prescription_view');	
	}
        
        	function clinic_cancel_prescription($prsid=null,$episode=null,$episode_id=null){
		$data = array();
		if (!$prsid){
			$data["error"] = "Prescription  not found";
		}
		if (!$episode){
			$data["error"] = "Prescription  not found";
		}
		if (!$episode_id){
			$data["error"] = "Prescription  not found";
		}
		$sve_data = array(
			"Status"=>"Cancelled"
		);
		$data["prsid"]  = $prsid;
		$data["episode"]  = $episode;
		$data["episode_id"]  = $episode_id;
		$this->load->model('mpersistent');
		$r = $this->mpersistent->update("clinic_prescription","clinic_prescription_id",$prsid,$sve_data);
		$data["sts"]  = $r;
		$this->load->vars($data);
        $this->load->view('clinic_cancel_prescription_view');	
	}
	
	public function clinic_dispense($prisid){
		if(!isset($prisid) ||(!is_numeric($prisid) )){
			$data["error"] = "Prescription  not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');	
			return;
		}
		$this->load->model('mpersistent');
		$this->load->model('mclinic');
		$this->load->helper('string');
		$data["clinic_prescription_info"] = $this->mpersistent->open_id($prisid, "clinic_prescription", "clinic_prescription_id");
		if (empty($data["clinic_prescription_info"])){
			$data["error"] ="Prescription not found";
			$this->load->vars($data);
			$this->load->view('pharmacy_error');
			return;
		}
		
		$data['title'] = 'Prescription dispensing';
		if ($data["clinic_prescription_info"]["Dept"] == "CLN"){
			//$this->load->model('mopd');
			if (isset($data["clinic_prescription_info"]["clinic_patient_id"])){
				$data["clinic_patient_info"] = $this->mpersistent->open_id($data["clinic_prescription_info"]["clinic_patient_id"], "clinic_visits", "clinic_visits_id");
				$data["clinic_info"] = $this->mclinic->get_clinic_info($data["clinic_patient_info"]["clinic"]);
				//$data["stock_info"] = $this->mpersistent->open_id($data["clinic_info"]["drug_stock"],"drug_stock", "drug_stock_id");
                        $UID=$this->session->userdata('UID');
                        $data["stock_info"] = $this->mclinic->get_pharm_stock($UID);
                        if($data["stock_info"]==NULL){
			$data["stock_info"] = $this->mpersistent->open_id($data["clinic_info"]["drug_stock"],"drug_stock", "drug_stock_id");
                        }
			}

			$data["prescribe_items_list"] =$this->mclinic->get_prescribe_items($prisid);
			//print_r($data["prescribe_items_list"]);
			//exit;
			if(isset($data["prescribe_items_list"])){
				for ($i=0;$i<count($data["prescribe_items_list"]); ++$i){
					//if ($data["prescribe_items_list"][$i]["drug_list"] == "who_drug"){
						$drug_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["DRGID"], "who_drug", "wd_id");
if($data["prescribe_items_list"][$i]["Dosage"]!='')$dose_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["Dosage"], "drugs_dosage", "Dosage");
if($data["prescribe_items_list"][$i]["Frequency"]!='')$freq_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["Frequency"], "drugs_frequency", "Frequency");
if($data["prescribe_items_list"][$i]["HowLong"]!='')$period_info = $this->mpersistent->open_id($data["prescribe_items_list"][$i]["HowLong"], "drugs_period", "Period");
						$drug_count=$this->mclinic->get_drug_count($data["stock_info"]["drug_stock_id"],$data["prescribe_items_list"][$i]["DRGID"]);
					//}	
					$data["prescribe_items_list"][$i]["drug_name"] = $drug_info["name"];
					$data["prescribe_items_list"][$i]["drug_dose"] = $drug_info["dose"];
					$data["prescribe_items_list"][$i]["drug_formulation"] = $drug_info["formulation"];
if(isset($dose_info["Factor"])) $data["prescribe_items_list"][$i]["dose_factor"]= $dose_info["Factor"];
if(isset($freq_info["Factor"])) $data["prescribe_items_list"][$i]["freq_factor"]= $freq_info["Factor"];
if(isset($period_info["Factor"])) $data["prescribe_items_list"][$i]["period_factor"]= $period_info["Factor"];
                    			$data["prescribe_items_list"][$i]["drug_count"] = $drug_count;
				}
			}
			$data['title'] = $data["clinic_info"]["name"].' Prescription dispensing';
		}
		$data['PID'] = $data["clinic_prescription_info"]["PID"];
		$this->load->vars($data);
        $this->load->view('clinic_drug_dispense');	
	}	
	public function save_clinic_dispense(){
		if($_POST){
			$clinic_prescription_id = null;
			$drug_stock_id = null;
			$this->load->model('mpersistent');
			$this->load->model('mdrug_stock');
			//print_r($_POST);
			//exit;
			foreach ($_POST as $k => $v) {
				if ($k == "clinic_prescription_id"){
					$clinic_prescription_id = $v;		
				}
				elseif ($k == "drug_stock_id"){
					$drug_stock_id = $v;
				}
				else{
					if ($k[0]!="_"){
						if(isset($_POST["_"+$k])){
							$drug_id = $_POST["_$k"];
						}
						else{
							$drug_id  = null;
						}
						$save_data = array(
							"Quantity" => $v,
							"Status" => "Dispensed"
						);
					
						//update($table=null,$key_field=null,$id=null,$data)
						$r = $this->mpersistent->update("clinic_prescribe_items","clinic_prescribe_item_id",$k,$save_data);
						if ($r){
							$this->mdrug_stock->deduct_drug($drug_stock_id, $drug_id , $v);
						}
					}
				}
			}
			$save_data = array(
				"Status" => "Dispensed"
			);
			$this->mpersistent->update("clinic_prescription","clinic_prescription_id",$clinic_prescription_id,$save_data);
			$this->session->set_flashdata(
				'msg', 'REC: ' . 'Dispensed'
			);
			$this->clinic_dispense($clinic_prescription_id);
		}
	}
} 


//////////////////////////////////////////

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */