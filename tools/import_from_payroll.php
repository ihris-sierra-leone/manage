#!/usr/bin/php
<?php
require_once("./import_base.php");



/*********************************************
*
*      Process Class
*
*********************************************/

class InfiniumProcessor extends Processor {
		
  protected $create_new_people = null;
    public function __construct($file) {
      parent::__construct($file);
    }

    protected function mapData() {
        $mapped_data = parent::mapData();                
        return $mapped_data;
    }

    protected function getExpectedHeaders() {
        return  array(
        'EMP_ID'=>'EMP ID',
        'FACILITY'=>'RCDESC',
        'FACILITYCODE'=>'RC',
        'FULLNAME'=>'nom',
        'DEPT_CODE'=>'Deptcode',
        'DEP_NAME'=>'descr2fcb1',
        'SALARY'=>'BASIC',
        'NASSIT'=>'NASSIT',
        'NASSITNO'=>'NASSITNO',
        'GRADE'=>'GRADE',
        'LEVEL'=>'LEVEL',
        'DOB'=>'dateofbirth',
        'POSITION_CODE'=>'position',
        'POSITION_DESC'=>'POSITION_DESC1',
            );
    }
       protected static $required_cols_by_transaction = array(
        'NE'=>array('NASSITNO')
        );


    protected $effective_date;
    protected function _processRow() {
        if (!$this->verifyData()) {
            return false;
        }
        $success = false;

            if (!$persID = $this->createNewPerson()) 
	{
            break;
        }                      
            $success = $this->setNewPosition("person|".$persID);
            $personObj->cleanup();
        return $success;
    }



    function verifyData() {
        
        $missing_cols = array();
        foreach (self::$required_cols_by_transaction["NE"] as $required_col) {
            if ($this->mapped_data[$required_col] === false || (is_string($this->mapped_data[$required_col]) && strlen($this->mapped_data[$required_col]) == 0)) {
                $missing_cols[] = $required_col;
            }
        }
        if (count($missing_cols) > 0) {
            $this->addBadRecord("Missing required columns " . implode(" ",$missing_cols));
		$codearr=explode("-",$this->mapped_data["CHECK_NO"]);
		if(count($codearr)>1)
		{
		$_SESSION["depcode"]=trim($codearr[0]);
		}    
            return false;
        }
        return true;
    }

    function createNewPerson() {
    	$fullname=$this->mapped_data['FULLNAME'];
    	$name_arr=explode(",",$fullname);
    	$surname=$name_arr[0];
    	$surname=ucwords(strtolower($surname));
    	$remaining_names=explode(" ",$name_arr[1]);
    	if(count($remaining_names)>1) {
    		$firstname=$remaining_names[count($remaining_names)-1];
    		$firstname=ucwords(strtolower(trim($firstname)));
    		unset($remaining_names[count($remaining_names)-1]);
    		$othernames=implode(" ",$remaining_names);
    		$othernames=ucwords(strtolower(trim($othernames)));
    	}
    	else if(count($remaining_names)==1) {
    		$firstname=ucwords(strtolower(trim($name_arr[1])));
    	}
      $personObj = $this->ff->createContainer('person');
      $personObj->firstname = $firstname;
      $personObj->surname = $surname;
      $personObj->othername = $othernames;
		$personID = $this->save($personObj,false);
		
		//Adding Demographic Info
		$demObj = $this->ff->createContainer('demographic');
		$dob=gmdate("Y-m-d",(($this->mapped_data['(DOB'] - 25569) * 86400));
		$demObj->getField("birth_date")->setFromDB($dob);
		$demObj->getField("parent")->setFromDB("person|".$personID);
		//End of adding Demographic
		
		//Add identification
		if($this->mapped_data['NASSITNO']!="") {
			$personIDObj = $this->ff->createContainer('person_id');
			$personIDObj->getField("id_type")->setFromDB("id_type|4");
			$personIDObj->getField("id_num")->setValue($this->mapped_data['NASSITNO']);
			$personIDObj->getField("parent")->setFromDB("person|".$personID);
			$this->save($personIDObj,false);
		}
      return $personID;
    }



    function setNewPosition($persID) {
    	//add facility if not exist
    	$faccode=str_replace($this->mapped_data['FACILITYCODE'],"",$this->mapped_data['FACILITY']);
    	$facname=ucwords(strtolower(trim($this->mapped_data['FACILITY'])));
    	$where=array(	"operator"=>"AND",
    						"operand"=>array(0=>array(	"operator"=>"FIELD_LIMIT",
    															"field"=>"name",
    															"style"=>"equals",
    															"data"=>array("value"=>$facname)),
    											  1=>array(	"operator"=>"FIELD_LIMIT",
    															"field"=>"dutyStationCode",
    															"style"=>"equals",
    															"data"=>array("value"=>$faccode))
    											 ));
    						
    	$facilities=I2CE_FormStorage::search("facility",false,$where);
    	if(count($facilities)==1) {
    		$facID="facility|".$facilities[0];
    	}
    	else if(count($facilities)==0) {
    		$facObj=$this->ff->createContainer('facility');
    		$facObj->getField("name")->setValue($facname);
    		$facObj->getField("dutyStationCode")->setValue($faccode);
    		$facID=$this->save($facObj,false);
    		$facID="facility|".$facID;
    	}
		//End of adding new facility
		
		//Adding Job
		$jobtitle=ucwords(strtolower(trim($this->mapped_data["POSITION_DESC"])));
		$where=array(	"operator"=>"FIELD_LIMIT",
							"field"=>"title",
							"style"=>"equals",
							"data"=>array("value"=>$jobtitle));
		$jobs=I2CE_FormStorage::search("job",false,$where);
		if(count($jobs)==1) {
			$jobID="job|".$jobs[0];
		}
		else if(count($jobs)==0) {
			$jobObj=$this->ff->createContainer('job');
    		$jobObj->getField("title")->setValue($jobtitle);
    		$jobID=$this->save($jobObj,false);
    		$jobID="job|".$jobID;
		}
		//End of adding job
		
		//creating position
		$posObj=$this->ff->createContainer('position');
		$posObj->getField("facility")->setFromDB($facID);
		$posObj->getField("job")->setFromDB($jobID);
		$posObj->getField("title")->setValue($jobtitle);
		$posObj->getField("proposed_salary")->setValue($this->mapped_data["SALARY"]);
		$posObj->getField("code")->setValue($this->mapped_data["POSITION_CODE"]);
		$posID=$this->save($posObj);
		$posID="position|".$posID;
		//End of creating Position
		
		//Setting position
		$persposObj=$this->ff->createContainer('person_position');
		$persposObj->getField("position")->setFromDB($posID);
		$persposObj->getField("parent")->setFromDB($persID);
		$persPosID=$this->save($persposObj);
		//End of setting position
		
		//adding salary to set position
		$salObj=$this->ff->createContainer('salary');
		$salObj->getField("salary")->setValue($this->mapped_data["SALARY"]);
		$salObj->getField("parent")->setFromDB("person_position|".$persPosID);
		$this->save($salObj);
      return true;
   }
}

/*********************************************
*
*      Execute!
*
*********************************************/


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


$processor = new InfiniumProcessor($file);
$processor->run();

echo "Processing Statistics:\n";
print_r( $processor->getStats());
