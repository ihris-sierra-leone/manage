<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
*
* This File is part of I2CE
*
* I2CE is free software; you can redistribute it and/or modify
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
*  iHRIS_Module_Leave
* @package I2CE
* @subpackage Core
* @author Ally Shaban <allyshaban5@gmail.com>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc.
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License as published by the Free Software Foundation; either
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Module_Leave extends I2CE_Module {

    public static function getMethods() {
      return array(
          'iHRIS_PageView->action_leave_request' => 'action_leave_request'
          );
    }

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
      return array(
              'validate_form_leave_request' => 'validate_form_leave_request',
              'validate_form_leave_category' => 'validate_form_leave_category',
              'validate_form_leave_cycle' => 'validate_form_leave_cycle',
              );
    }


    public function getLeaveDetails($leave_category,$person_id) {
      $this->factory = I2CE_FormFactory::instance();
      $leaveCatObj = $this->factory->createContainer($leave_category);
      $leaveCatObj->populate();
      $leave_cat_name = $leaveCatObj->getField("name")->getDisplayValue();
      $leave_period = "Last ".$leaveCatObj->getField("leave_cycle")->getDisplayValue();
      $maxDays = $leaveCatObj->getField("days")->getDisplayValue();
      $start_period = $leaveCatObj->getField("start_date")->getDBValue();
      $end_period = $leaveCatObj->getField("end_date")->getDBValue();
      $leave_cycle = $leaveCatObj->getField("leave_cycle")->getDBValue();
      $balance = 0;
      $days_on_leave = 0;
      if($leave_cycle) {
        $levCycleObj = $this->factory->createContainer($leave_cycle);
        $levCycleObj->populate();
        $leaveCycle = $levCycleObj->getField("years")->getDBValue();

        //get all rapproved requested leave
        $years = date("Y") - $leaveCycle;
        $check_date = $years."-".date("m-d");
        $where = array( "operator"=>"AND",
                        "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"leave_status",
                                                  "style"=>"equals",
                                                  "data" => array("value" => "leave_status|approved")),
                                         1=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"start_date",
                                                  "style"=>"greaterthan",
                                                  "data" => array("value" => $check_date)),
                                         2=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"leave_category",
                                                  "style"=>"equals",
                                                  "data" => array("value" => $leave_category)),
                                         3=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"parent",
                                                  "style"=>"equals",
                                                  "data" => array("value" => $person_id)),
                                         4=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"leave_category",
                                                  "style"=>"equals",
                                                  "data" => array("value" => $leave_category)))
                      );
        $leav_req_arr = I2CE_FormStorage::search("leave_request",false,$where);
        if(count($leav_req_arr) == 0) {
          $balance = $maxDays;
        }
        else {
          $balance = $maxDays;
          foreach($leav_req_arr as $leave_req_id) {
            $leavReqObj = $this->factory->createContainer("leave_request|".$leave_req_id);
            $leavReqObj->populate();
            $days = $leavReqObj->getField("leave_days")->getDBValue();
            $balance = $balance - $days;
            $days_on_leave = $days_on_leave + $days;
          }
        }
      }
      if(!$leave_cycle) {
        $start_period = explode("-",$start_period);
        $start_period_month = $start_period[1];
        $end_period = explode("-",$end_period);
        $end_period_month = $end_period[1];
        if($start_period_month > $end_period_month) {
          $current_year = date("Y");
          $current_month = date("m");
          if($current_month >= $start_period_month) {
            $leave_period = $current_year."-".$start_period_month."/" . ++$current_year . "-" . $end_period_month;
          }
          else if($current_month < $start_period_month) {
            $leave_period = --$current_year."-".$start_period_month."/" . $current_year . "-" . $end_period_month;
          }
        }
        else if($start_period_month <= $end_period_month) {
          $current_year = date("Y");
          $current_month = date("m");
          if($current_month >= $start_period_month) {
            $leave_period = $current_year."-".$start_period_month."/" . $current_year . "-" . $end_period_month;
          }
          else if($current_month < $start_period_month) {
            $leave_period = --$current_year."-".$start_period_month."/" . --$current_year . "-" . $end_period_month;
          }
        }
        //calculating leave balance
        $check_date = explode("/",$leave_period);
        $check_date_start = $check_date[0] . "-" . "01";
        $date = new DateTime($check_date[1]);
        //getting the last day of the month
        $check_date_end = $date->format("t");
        $where = array( "operator"=>"AND",
                        "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"leave_status",
                                                  "style"=>"equals",
                                                  "data" => array("value" => "leave_status|approved")),
                                         1=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"start_date",
                                                  "style"=>"greaterthan",
                                                  "data" => array("value" => $check_date_start)),
                                         2=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"end_date",
                                                  "style"=>"lessthan",
                                                  "data" => array("value" => $check_date_end)),
                                         3=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"parent",
                                                  "style"=>"equals",
                                                  "data" => array("value" => $person_id)),
                                         4=>array("operator"=>"FIELD_LIMIT",
                                                  "field"=>"leave_category",
                                                  "style"=>"equals",
                                                  "data" => array("value" => $leave_category)))
                      );
        $leav_req_arr = I2CE_FormStorage::search("leave_request",false,$where);
        if(count($leav_req_arr) == 0) {
          $balance = $maxDays;
        }
        else {
          $balance = $maxDays;
          foreach($leav_req_arr as $leave_req_id) {
            $leavReqObj = $this->factory->createContainer("leave_request|".$leave_req_id);
            $leavReqObj->populate();
            $days = $leavReqObj->getField("leave_days")->getDBValue();
            $balance = $balance - $days;
            $days_on_leave = $days_on_leave + $days;
          }
        }
      }
      $leave_details = array( "leave_cat_name"=>$leave_cat_name,
                              "leave_period"=>$leave_period,
                              "max_days"=>$maxDays,
                              "balance"=>$balance,
                              "days_on_leave"=>$days_on_leave
                            );
      return $leave_details;
    }

    public function action_leave_request($obj) {
      $person_id=$obj->getPerson()->getNameId();
      $this->factory = I2CE_FormFactory::instance();
      if (!$obj instanceof iHRIS_PageView) {
          I2CE::raiseError("invalid call");
          return false;;
      }

      //displaying one latest leave request for every category
      $template = $obj->getTemplate();
      $cat = I2CE_FormStorage::search("leave_category");
      if(count($cat > 0)) {
        foreach($cat as $index=>$leave_cat) {
          $leave_cat_id = 'leave_category|'.$leave_cat;
          $where = array( "operator"=>"AND",
                          "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                    "field"=>"leave_category",
                                                    "style"=>"equals",
                                                    "data" => array("value" => $leave_cat_id)),
                                           1=>array("operator"=>"FIELD_LIMIT",
                                                    "field"=>"parent",
                                                    "style"=>"equals",
                                                    "data" => array("value" => $person_id)))
                        );
          $search_leave = I2CE_FormStorage::search("leave_request",false,$where,array("-created"),true);
          if($search_leave) {
            $leave_request_node = $template->appendFileById("view_leave_request.html", "div", "leave_request");
            $leave_request_id = "leave_request|".$search_leave;
            $leaveReqObj=$this->factory->createContainer($leave_request_id);
            $leaveReqObj->populate();
            $template->setForm($leaveReqObj, $leave_request_node);
          }
        }
      }

      //displaying leave balance
      $persObj = $this->factory->createContainer($person_id);
      $persObj->populate();
      $persObj->populateChildren('demographic');
      $dem_gender = '';
      foreach($persObj->getChildren('demographic') as $demObj) {
        $demObj->populate();
        $dem_gender = $demObj->getField('gender')->getDBValue();
      }
      if($dem_gender == 'gender|F') {
        $cat_gender = 'female';
      }
      else if($dem_gender == 'gender|M') {
        $cat_gender = 'male';
      }
      $where = array ('operator'=>'OR',
                      'operand'=>array(0=>array('operator'=>'FIELD_LIMIT',
                                                'field'=>'gender',
                                                'style'=>'equals',
                                                'data'=>array('value'=>$cat_gender)
                                                ),
                                        1=>array('operator'=>'FIELD_LIMIT',
                                                'field'=>'gender',
                                                'style'=>'equals',
                                                'data'=>array('value'=>'all')
                                                ),
                                      )
                      );
      $cat = I2CE_FormStorage::search("leave_category",false,$where);
      if(count($cat > 0)) {
        $template = $obj->getTemplate();
    		$person_id=$obj->getPerson()->getNameId();
        $persObj=$this->factory->createContainer($person_id);
        $leave_balance_node = $template->appendFileById("view_leave_balance.html", "div", "leave_balance");
        foreach($cat as $index=>$leave_cat) {
          $row_node = $template->appendFileByName( "view_leave_balance_rows.html", "tr", "leave_balance_rows", 0, $leave_balance_node);

          $leaveDetails = $this->getLeaveDetails('leave_category|'.$leave_cat,$person_id);
          $template->setDisplayDataImmediate( "leave_balance_counter", ++$index, $row_node );
          $template->setDisplayDataImmediate( "leave_balance_period", $leaveDetails["leave_period"], $row_node );
          $template->setDisplayDataImmediate( "leave_balance_category", $leaveDetails["leave_cat_name"], $row_node );
          $template->setDisplayDataImmediate( "leave_balance_max_days", $leaveDetails["max_days"], $row_node );
          $template->setDisplayDataImmediate( "leave_balance_leave_days", $leaveDetails["days_on_leave"], $row_node );
          $template->setDisplayDataImmediate( "leave_balance_balance", $leaveDetails["balance"], $row_node );
        }
      }

      //return $obj->addChildForms('leave_request');
    }

    public function validate_form_leave_category( $form ) {
      $this->factory = I2CE_FormFactory::instance();
        //if leave cycle is not selected,then make sure dates are valid and selected
        if($form->leave_cycle[0] != "leave_cycle") {
          if ( $form->start_date->isValid() && $form->end_date->isValid() ) {
              if ( $form->start_date->compare( $form->end_date ) == 0 ) {
                  $form->setInvalidMessage('end_date','Start date should not be equal to end date');
              }
          }
        }
    }

    public function validate_form_leave_cycle( $form ) {
      $this->factory = I2CE_FormFactory::instance();
        //if leave cycle is not selected,then make sure dates are valid and selected
        if(!ctype_digit(strval($form->years)))
        $form->setInvalidMessage('years','This must be an integer');
    }

    public function validate_form_leave_request( $form ) {
      $this->factory = I2CE_FormFactory::instance();
      if ( $form->start_date->isValid() && $form->end_date->isValid() && $form->return_date->isValid()) {
          if ( $form->start_date->compare( $form->end_date ) == 0 ) {
              $form->setInvalidMessage('end_date','Start date should not be equal to end date');
          }
          else if ( $form->start_date->compare( $form->end_date ) < 0 ) {
              $form->setInvalidMessage('end_date','End date should not come before start date');
          }
          else if ( $form->end_date->compare( $form->return_date ) == 0 ) {
              $form->setInvalidMessage('return_date','Return date should not be equal to end date');
          }
          else if ( $form->end_date->compare( $form->return_date ) < 0 ) {
              $form->setInvalidMessage('return_date','Return date should not come before end date');
          }
      }
      else
      $form->setInvalidMessage('end_date','Invalid Dates');

      $leave_category = implode("|",$form->leave_category);
      $leave_details = $this->getLeaveDetails($leave_category,$form->getParent());
      $leaveCatObj = $this->factory->createContainer($leave_category);
      $leaveCatObj->populate();
      $leave_cat_name = $leaveCatObj->getField("name")->getDisplayValue();
      if($leave_details["max_days"] == $leave_details["days_on_leave"]) {
        $form->setInvalidMessage('leave_category','You have used all your leave days for '.$leave_cat_name.' On this period');
      }
      else if($leave_details["balance"] < $form->leave_days) {
        $form->setInvalidMessage('end_date','Days Requested Exceeds Leave Balance Of '.$leave_details["balance"]);
      }

      //deny leave request within leave period of the same leave category
      $search_leave = I2CE_FormStorage::search("leave_request");
      $today = date("Y-m-d");
      $where = array( "operator"=>"AND",
                      "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"leave_status",
                                                "style"=>"equals",
                                                "data" => array("value" => "leave_status|approved")),
                                       1=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"start_date",
                                                "style"=>"lessthan_equals",
                                                "data" => array("value" => $today)),
                                       2=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"end_date",
                                                "style"=>"greaterthan_equals",
                                                "data" => array("value" => $today)),
                                       3=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"parent",
                                                "style"=>"equals",
                                                "data"=>array("value"=>$form->getParent())),
                                       4=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"leave_category",
                                                "style"=>"equals",
                                                "data" => array("value" => $leave_category)))
                    );
      $search_leave = I2CE_FormStorage::search("leave_request",false,$where);
      if(count($search_leave)>0) {
        return $form->setInvalidMessage('leave_category','You are already on '.$leave_cat_name.' You can only request another '.$leave_cat_name.' After this has ended');
      }

      //deny leave request within leave period of the same leave category
      $start_date = implode("-",$form->start_date->getValues());
      $start_date = date("Y-m-d",strtotime($start_date));
      $search_leave = I2CE_FormStorage::search("leave_request");
      $where = array( "operator"=>"AND",
                      "operand"=>array(0=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"leave_status",
                                                "style"=>"equals",
                                                "data" => array("value" => "leave_status|approved")),
                                       1=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"end_date",
                                                "style"=>"greaterthan_equals",
                                                "data" => array("value" => $start_date)),
                                       2=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"parent",
                                                "style"=>"equals",
                                                "data"=>array("value"=>$form->getParent())),
                                       3=>array("operator"=>"FIELD_LIMIT",
                                                "field"=>"leave_category",
                                                "style"=>"equals",
                                                "data" => array("value" => $leave_category)))
                    );
      $search_leave = I2CE_FormStorage::search("leave_request",false,$where);
      if(count($search_leave)>0) {
        return $form->setInvalidMessage('leave_category','You are already on '.$leave_cat_name.' You can only request another '.$leave_cat_name.' After this has ended');
      }
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
