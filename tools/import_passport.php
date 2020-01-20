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
 * @author Sovello Hildebrand sovellohpmgani@gmail.com
 * @copyright Copyright &copy; 2007, 2008-2013 IntraHealth International, Inc. 
 * @version 4.6.0
 */

require_once("./import_base.php");

class PersonalData_Import extends Processor{
    
    public function __construct($file,$passport_dir) {
        parent::__construct($file);
        $this->passport_dir=$passport_dir;
    }
    
    //map headers from the spreadsheet
    //what you do here is change the values on the right to match what you have on the spreadsheet. comment out lines that are not in the spreadsheet
    //the values of the left are used by the script to refer to the spreadsheet columns on the right of this array.
    //the order of the columns in the spreadsheet doesn't matter
    
    protected function getExpectedHeaders(){
        return array(
          'pin' => 'PIN',
          'image' => 'HW_image',
        );
      }

    protected static $required_cols_by_transaction = array(
        'NE'=>array('pin','image')
        );

   protected function _processRow() {
      $success = true;
      $this->add_passport();
      return $success;
      }
      
	protected function add_passport() {
	  $passport_photos = glob("$this->passport_dir/*jpg");
	  $pin_code=$this->mapped_data['pin'];
	  $passport_name=str_replace("Media/","",$this->mapped_data['image']);
	  if(in_array($this->passport_dir."/".$passport_name,$passport_photos)) {
	  	$passport_photo=$this->passport_dir."/".$passport_name;
	  	$where=array(	"operator"=>"AND",
	  						"operand"=>array(	0=>array("operator"=>"FIELD_LIMIT",
	  															"field"=>"id_type",
	  															"style"=>"equals",
	  															"data"=>array("value"=>"id_type|1")),
	  											  	1=>array("operator"=>"FIELD_LIMIT",
	  															"field"=>"id_num",
	  															"style"=>"equals",
	  															"data"=>array("value"=>$pin_code))
	  					 ));
	  	$IDs=I2CE_FormStorage::search("person_id",false,$where);
	  	if(count($IDs)==1) {
	  		$IDObj=$this->ff->createContainer("person_id|".current($IDs));
	  		$IDObj->populate();
	  		$parent=$IDObj->getParent();
	  		$personObj = $this->ff->createContainer('person_photo_passport');
	  		$getfile = file_get_contents($passport_photo, true);
       	$personObj->getField('image')->setFromData($getfile, basename($passport_photo) ); //set the file name
       	$personObj->getField('description')->setFromDB(basename($passport_photo));
       	$personObj->setParent($parent);
       	$this->save($personObj);
       	$personObj->cleanup();
	  	}
	  	else if(count($IDs)>1) {
	  		I2CE::raiseError("More than one person found with pincode $pin_code,Skip processing");
	  		return;
	  	}
	  	return;
	  												
	  }
     }
}


/*********************************************
*
*      Execute!
*
*********************************************/

//ini_set('memory_limit','3000MB');


if (count($arg_files) != 2) {
    usage("Please specify the name of a spreadsheet to process");
}

reset($arg_files);
$file = current($arg_files);
$passport_dir=next($arg_files);
if($file[0] == '/') {
    $file = realpath($file);
} else {
    $file = realpath($dir. '/' . $file);
}
if (!is_readable($file)) {
    usage("Please specify the name of a spreadsheet to import: " . $file . " is not readable");
}

I2CE::raiseMessage("Loading from $file");


$processor = new PersonalData_Import($file,$passport_dir);
$processor->run();

echo "Processing Statistics:\n";
print_r( $processor->getStats());




# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
