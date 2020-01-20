#!/usr/bin/php
<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 *
 * This File is part of iHRIS
 *
 * iHRIS is free software; you can redistribute it and/or modify
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * The page wrangler
 *
 * This page loads the main HTML template for the home page of the site.
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Juma Lungo <juma.lungo@zalongwa.com>
 * @copyright Copyright &copy; 2017, 2008-2018 IntraHealth International, Inc.
 * @version 4.2.0
 */

 /*
Below are imported data elements
Firstname,second name,third name,surname,employment date,education level,gender,birth_date,facility,job
 */
require_once("./import_base.php");

class PersonalData_Import extends Processor{

    public function __construct($file) {
        parent::__construct($file);
    }
	protected $prefixlist = array(	
		"Dr" => "Dr.",
		"DR." => "Dr.",
		"Dr." => "Dr.",
		
    );

	protected $maritallist = array(	
		"Divorced" => "Divorced",
		"Married" => "Married",
		"Single" => "Single",
		"Widowed" => "Widowed",
    );

    //map headers from the spreadsheet
    //what you do here is change the values on the right to match what you have on the spreadsheet. comment out lines that are not in the spreadsheet
    //the values of the left are used by the script to refer to the spreadsheet columns on the right of this array.
    //the order of the columns in the spreadsheet doesn't matter

    protected function getExpectedHeaders() {
        return array(
          'first_name' => 'First Name',
          'second_name' => 'Other Names',
          'surname' => 'Surname',
          'birth_date' => 'DoB',
          'gender' => 'Gender',          
          'facility' => 'PHU',
          'facility_type' => 'Facility Type',
          'cadre' => 'Cadre',
          'region' => 'Province',
          'district' =>'District',
          'county' => 'Chiefdom',
          'delete_all' =>'Delete/Update',
          'identification_number' => 'ID NUMBER',
          'telephone'=>'Phone Number'       
        );
    }

    protected static $required_cols_by_transaction = array(
        'NE'=>array(
          'first_name',
          'second_name',
          'surname',
          'birth_date',
          'gender',          
          'facility',
          'facility_type',
          'cadre',
          'region',
          'district',
          'county',
          'delete_all',
          'telephone'
        )
    );
   
   protected function _processRow() {
      $success = true;
      $this->create_person();
      return $success;
   }

   protected function create_person()
   {
       $personObj = $this->ff->createContainer('person');
       $first_name = ucfirst(strtolower($this->mapped_data['first_name']));
       $second_name = ucfirst(strtolower($this->mapped_data['second_name']));
       $surname = ucfirst(strtolower($this->mapped_data['surname']));

       $delete_all = trim($this->mapped_data["delete_all"]);
       echo "Delete/Update value in Column BT is: ".$delete_all;
       if(trim($this->mapped_data["delete_all"])=="Delete") {
               $ids = I2CE_FormStorage::search('person_id');
               echo "Deleting " . sizeof($ids) . " person_id records\n";
               foreach ($ids as $id) {
                   $delObj = $this->ff->createContainer('person_id|' .$id);
                   $delObj->populate();
                   $delObj->delete();
               }
               // delete demographic
               $ids = I2CE_FormStorage::search('demographic');
               echo "Deleting " . sizeof($ids) . " demographic records\n";
               foreach ($ids as $id) {
                   $delObj = $this->ff->createContainer('person_id|' .$id);
                   $delObj->populate();
                   $delObj->delete();
               }
               //delete contact
               $ids = I2CE_FormStorage::search('person_contact_personal');
               echo "Deleting " . sizeof($ids) . " person_contact_personal records\n";
               foreach ($ids as $id) {
                   $delObj = $this->ff->createContainer('person_id|' .$id);
                   $delObj->populate();
                   $delObj->delete();
               }
               //delete current position
               $ids = I2CE_FormStorage::search('person_position');
               echo "Deleting " . sizeof($ids) . " person_position records\n";
               foreach ($ids as $id) {
                   $delObj = $this->ff->createContainer('person_id|' .$id);
                   $delObj->populate();
                   $delObj->delete();
               }

               //delete current position
               $ids = I2CE_FormStorage::search('position');
               echo "Deleting " . sizeof($ids) . " position records\n";
               foreach ($ids as $id) {
                   $delObj = $this->ff->createContainer('person_id|' .$id);
                   $delObj->populate();
                   $delObj->delete();
               }

               //delete person
               $ids = I2CE_FormStorage::search('person');
               echo "Deleting " . sizeof($ids) . " person records\n";
               foreach ($ids as $id) {
                   $delObj = $this->ff->createContainer('person_id|' .$id);
                   $delObj->populate();
                   $delObj->delete();
               }

       }
                $nationality_id = "country|SL";
		if($nationality_id) {
		   $personObj->getField("nationality")->setFromDB($nationality_id);
		}
		
		$personObj->getField("firstname")->setValue($first_name);
		$personObj->getField("othername")->setValue($second_name);
		$personObj->getField("surname")->setValue($surname);
		$this->person_id=$this->save($personObj);
		$this->person_id="person|".$this->person_id;
		$personId = $this->person_id;
		$this->add_demographic();
		$position = $this->create_position();
		$this->assign_position($position);
		$this->add_contact();
		return $this->person_id;
   }

   protected function createPersonId($personId, $id_nums = array() ){
            $formObj = $this->ff->createContainer('person_id');
            //Search Id Type
            $idtype = "EmpIDNo"; //trim($this->mapped_data["identification_type"]);
            $idtype_id = $this->search_data("id_type",$idtype,"name");
            if(!$idtype_id) {
              // Create ID Type
              $idObj = $this->ff->createContainer('id_type');
              $idObj->getField("name")->setValue($idtype);
              $idtype_id = $this->save($idObj);
             }
            
            $formObj->getField('id_num')->setValue(trim($this->mapped_data['identification_number']));
            //$formObj->getField('issue_date')->setValue(trim($this->mapped_data['identification_date_of_issue']));
            //$formObj->getField('place')->setValue(trim($this->mapped_data['identification_issuing_authority']));
            if($idtype_id) {
                $formObj->getField('id_type')->setFromDB($idtype_id);
            }
            $formObj->getField("parent")->setValue($this->person_id);
            $this->save($formObj);
    }
    
   protected function add_demographic() {
        $demObj = $this->ff->createContainer('demographic');

        if($this->mapped_data['gender']=="Female") {
            $gender = "gender|F";
        }
        elseif($this->mapped_data['gender']=="Male") {
            $gender = "gender|M";
        }
        if($this->mapped_data['marital_status']=="Married") {
            $marital_status_id = "marital_status|2";
        }
        elseif($this->mapped_data['marital_status']=="Single") {
            $marital_status_id = "marital_status|1";
        }
        $dob = $this->format_date($this->mapped_data["birth_date"]);
        if($dob) {
           $demObj->getField("birth_date")->setFromDB($dob);
        }

        if($marital_status_id) {
            $demObj->getField("marital_status")->setFromDB($marital_status_id);
        }

        if($gender) {
            $demObj->getField("gender")->setFromDB($gender);
        }
        $demObj->getField("parent")->setValue($this->person_id);
       $this->save($demObj);
   }

   protected function add_contact() {
        $conObj = $this->ff->createContainer('person_contact_personal');
        $personal_mail = trim($this->mapped_data["personal_address"]);
        $personal_address =$personal_mail;
        $conObj->getField('address')->setValue($personal_address);
        $conObj->getField('mobile_phone')->setValue($this->mapped_data['telephone']);
        $conObj->getField('email')->setValue($this->mapped_data['email']);
        $conObj->getField('telephone')->setValue($this->mapped_data['telephone']);
        $conObj->getField("parent")->setValue($this->person_id);
        $this->save($conObj);
   }

   protected function create_position() {
        //Search Position status
        $posstatus = trim($this->mapped_data["pos_status"]);
        $posstatus_id = $this->search_data("position_type",$posstatus,"name");

        if(!$posstatus_id) {
            if(strlen($posstatus) >2) {
                // Create Position Status
                $posObj = $this->ff->createContainer('position_type');
                $posObj->getField("name")->setValue($posstatus);
                $posstatus_id = $this->save($posObj);
            }
        }
         // Search Province
	    $reg = trim($this->mapped_data["region"]);
	    $reg_id = $this->search_data("region",$reg,"name");
	    if(!$reg_id) {
	        if(strlen($reg) >2) {
                // create province if not exist
                $regObj = $this->ff->createContainer('region');
                $regObj->getField("name")->setValue($reg);
                $regObj->getField("country")->setFromDB($nationality_id);
                $reg_id = $this->save($regObj);
               }
	    }

	    $dis = trim($this->mapped_data["district"]);
	    $dis_id = $this->search_data("district",$dis,"name");

	    if(!$dis_id) {
	        if(strlen($dis) >2) {
                // create province if not exist
                $disObj = $this->ff->createContainer('district');
                $disObj->getField("name")->setValue($dis);
                $disObj->getField("region")->setFromDB($reg_id);
                $dis_id = $this->save($disObj);
               }
	    }
	    
	    //Search Chiefdom
	    $ward = trim($this->mapped_data["county"]);
	    $ward_id = $this->search_data("county",$ward,"name");

	    if(!$ward_id) {
	        if(strlen($ward) >2) {
                // create ward if not exist
                $waObj = $this->ff->createContainer('county');
                $waObj->getField("name")->setValue($ward);
                $waObj->getField("district")->setFromDB($dis_id);
                $ward_id = $this->save($waObj);
               }
	    }
        //Search Facility
        $facility = trim($this->mapped_data["facility"]);
        $facility_id = $this->search_data("facility",$facility,"name");

        //Search Facility Types
        $facilitytype = trim($this->mapped_data["facility_type"]);
        $facilitytype_id = $this->search_data("facility_type",$facilitytype,"name");

        if(!$facilitytype_id) {
            if(strlen($facilitytype) >2) {
                // Create Facility Type
                $fctObj = $this->ff->createContainer('facility_type');
                $fctObj->getField("name")->setValue($facilitytype);
                $facilitytype_id = $this->save($fctObj);
            }
        }

	    if(!$facility_id) {
		    // create facility if not exist
		    $facObj = $this->ff->createContainer('facility');
		    $facObj->getField("name")->setValue($facility);
		    $facObj->getField("facility_type")->setFromDB($facilitytype_id);
		    if($ward_id) {
		    	$facObj->getField("location")->setFromDB($ward_id);
		    }
		    $facility_id = $this->save($facObj);
	    }

        // Search Cadre
        $cadre = trim(ucwords($this->mapped_data["cadre"]));
        $cad_id = $this->search_data("cadre",$cadre,"name");
        if(strlen($cadre) != 0) {
            $cad_id = $this->search_data("cadre", $job, "name");
            if (!$cad_id) {
                // create Rank if not exist
                $cadObj = $this->ff->createContainer('cadre');
                $cadObj->getField("name")->setValue($cadre);
                $cad_id = $this->save($cadObj);
            }

        }
        // Search Rank
        $job = trim(ucwords($this->mapped_data["cadre"]));
        /*
	    if(array_key_exists($job, $this->job_mapping)) {
                   $job = $this->job_mapping[$job];
	    }
*/
        if(strlen($job) != 0){
			$job_id = $this->search_data("job", $job, "title");
			if(!$job_id) {
				// create Rank if not exist
			 	$jobObj = $this->ff->createContainer('job');			
			 	$jobObj->getField("title")->setValue($job);
			 	$jobObj->getField("cadre")->setValue($cadre);
			 	$job_id = $this->save($jobObj);
			}
		
			if($job_id) {
				$posObj = $this->ff->createContainer('position');
				$posObj->getField("job")->setFromDB($job_id);
				$posObj->getField("title")->setValue($job);
				$posObj->getField("status")->setFromDB("position_status|closed");
				$facility = trim($this->mapped_data["facility"]);
				$facility_id = $this->search_data("facility",$facility,"name");	
				if($facility_id) {
		        		$posObj->getField("facility")->setFromDB($facility_id);
		    		}
				//$date = $this->format_date($this->mapped_data["empl_date"]);
				//$posObj->getField("posted_date")->setFromDB($date);
				//$grade = trim($this->mapped_data["pos_gradelevel"]);
				//$grade_id = $this->search_data("pos_gradelevel",$grade,"name");
		    		//if($grade_id) {
		        	//	$posObj->getField("pos_gradelevel")->setFromDB($grade_id);
		    		//}
				$id = $this->save($posObj);
				$position_id = "position|".$id;
				return $position_id;
			}
			else
				return false;
		     }
	}

	protected function assign_position($position) {
		if(!$position)
			return false;
		$persPosObj = $this->ff->createContainer('person_position');
		$persPosObj->getField("position")->setFromDB($position);
		/*
		//Search Position status
        	$posstatus = trim($this->mapped_data["pos_status"]);
        	$posstatus_id = $this->search_data("position_type",$posstatus,"name");
        	if(!$posstatus_id) {
            		// Create Position Status
            		$statObj = $this->ff->createContainer('position_type');
            		$statObj->getField("name")->setValue($posstatus);
            		$posstatus_id = $this->save($statObj);
        	}

        	//Search Grade
        	$posgrade = trim($this->mapped_data["pos_gradelevel"]);
         	if(strlen($posgrade) ==1) {
             		$posgrade='0'.$posgrade;
        	}
       		$posgrade_id = $this->search_data("salary_grade",$posgrade,"name");

		//Search Cadre
        	$poscadre = trim($this->mapped_data["pos_cadre"]);
        	$poscadre_id = $this->search_data("cadre",$poscadre,"name");
        	if(!$poscadre_id) {
            		// Create Position Status
            		$cadPosObj = $this->ff->createContainer('cadre');
            		$cadPosObj->getField("name")->setValue($poscadre);
            		$poscadre_id = $this->save($cadPosObj);
        	}
		*/
       		
        	//$persPosObj->getField("pos_status")->setFromDB($posstatus_id);
        	//$persPosObj->getField("pos_cadre")->setFromDB($poscadre_id);
        	//$persPosObj->getField("pos_gradelevel")->setFromDB($posgrade_id);
        	//$dopa = $this->format_date($this->mapped_data["dopa"]);
		//$persPosObj->getField("start_date")->setFromDB($dopa);
		$persPosObj->getField("parent")->setValue($this->person_id);
		$id = $this->save($persPosObj);
		return $id;
	}

	public function format_date($date) {
		if(strlen($date) > 5){
			//$date = str_replace("/","-",$date);
			$date = date("Y-m-d",strtotime($date));
		}
		else if(strlen($date) == 5) {
			$date = ($date-25569)*86400;
			$date = gmdate("Y-m-d",$date);
		}
		return $date;
	}

	protected function search_data($form,$data,$field) {
		$where=array(	"operator"=>"FIELD_LIMIT",
									"field"=>$field,
									"style"=>"equals",
									"data"=>array("value"=>$data)
								 );
		$exist=I2CE_FormStorage::search($form,false,$where);
		if(count($exist)==0) {
			return false;
		}
		else {
			return $form."|".$exist[0];
		}
	}

	protected function add_magicdata($form,$data,$field=false,$mapped_data=array()) {
		if(!$field)
		$field="name";
		$where=array(	"operator"=>"FIELD_LIMIT",
							"field"=>$field,
							"style"=>"equals",
							"data"=>array("value"=>$data)
						 );
		$exist=I2CE_FormStorage::search($form,false,$where);
		if(count($exist)==0) {
			$formObj=$this->ff->createContainer($form);
			$formObj->getField($field)->setValue($data);
			if(count($mapped_data)>0) {
				$formObj->getField($mapped_data["form"])->setFromDB($mapped_data["data"]);
			}
			$data_id=$this->save($formObj);
			return $form."|".$data_id;
			}
		else {
			return $form."|".$exist[0];
			}
		}

}

/*********************************************
*
*      Execute!
*
*********************************************/

//ini_set('memory_limit','3000MB');

if (count($arg_files) != 1) {
    usage("Please specify the name of a spreadsheet to process");
}

reset($arg_files);
$file = current($arg_files);
if($file[0] == '/') {
    $file = realpath($file);
} else {
    $file = realpath($dir. '/' . $file);
}
if (!is_readable($file)) {
    usage("Please specify the name of a spreadsheet to import: " . $file . " is not readable");
}

I2CE::raiseMessage("Loading from $file");

$processor = new PersonalData_Import($file);
I2CE::raiseMessage("Done reading file");
$processor->run();

echo "Processing Statistics:\n";
print_r( $processor->getStats());

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
