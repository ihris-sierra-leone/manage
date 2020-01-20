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
        'province'=>'province',
        'district'=>'district',
        'chiefdom'=>'chiefdom',
            );
    }
       protected static $required_cols_by_transaction = array(
        'NE'=>array('chiefdom')
        );


    protected $effective_date;
    protected function _processRow() {
        if (!$this->verifyData()) {
            return false;
        }
        $success = false;

            $persID = $this->addChiefdom();
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

    function addChiefdom() {
      $countyObj = $this->ff->createContainer('county');
      $countyObj->name = ucwords(strtolower(trim($this->mapped_data['chiefdom'])));
      $countyObj->getField("district")->setFromDB($this->mapped_data['district']);
		$this->save($countyObj,false);
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
