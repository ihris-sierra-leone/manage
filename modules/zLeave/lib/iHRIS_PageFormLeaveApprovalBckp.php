<?php
/**
* © Copyright 2007 IntraHealth International, Inc.
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
*  iHRIS_PageFormPerson
* @package I2CE
* @subpackage Core
* @author Ally Shaban <allyshaban5@gmail.com>
* @copyright Copyright &copy; 2007 IntraHealth International, Inc.
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License as published by the Free Software Foundation; either
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_PageFormLeaveApproval extends I2CE_PageForm{
  protected function action() {
    if (! ($leave_applicants_node = $this->template->getElementByID("leave_applicants")) instanceof DOMNode) {
      return ;
    }
    //get everyone with pending requests
    $where = array( "operator"=>"FIELD_LIMIT",
                    "field"=>"leave_status",
                    "style"=>"equals",
                    "data" => array("value" => "leave_status|waiting_approval"));
    $leave_requests = I2CE_FormStorage::search("leave_request",false,$where);

    if(count($leave_requests) == 0) {
      $this->userMessage("No Pending Leaves");
			$this->setRedirect("home");
    }

    foreach($leave_requests as $leave_request) {
      $leaveReqObj = $this->factory->createContainer("leave_request|".$leave_request);
      $leaveReqObj->populate();
      //leave cat info
      $leave_category = $leaveReqObj->getField("leave_category")->getDisplayValue();
      $leaveCatObj = $this->factory->createContainer($leaveReqObj->getField("leave_category")->getDBValue());
      $leaveCatObj->populate();

      $leaveCatName = $leaveCatObj->getField("name")->getDisplayValue();
      $maxDays = $leaveCatObj->getField("days")->getDisplayValue();
      $start_period = $leaveCatObj->getField("start_date")->getDBValue();
      $end_period = $leaveCatObj->getField("end_date")->getDBValue();
      $leave_cycle = $leaveCatObj->getField("leave_cycle")->getDBValue();
      /*
      calculating leave balance
      if leave cycle id defined then ignore start and end period which is based on date
      **/
      $balance = 0;
      $days_on_leave = 0;
      if($leave_cycle) {
        $levCycleObj = $this->factory->createContainer($leave_cycle);
        $levCycleObj->populate();
        $leaveCycle = $levCycleObj->getField("years")->getDBValue();
        $leave_period = "Last ".$leaveCatObj->getField("leave_cycle")->getDisplayValue();

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
                                                  "data" => array("value" => 'leave_category|'.$leave_cat)))
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
            $start_date = strtotime($leavReqObj->getField("start_date")->getDBValue());
            $end_date = strtotime($leavReqObj->getField("end_date")->getDBValue());
            $days = floor(($end_date-$start_date)/(60*60*24)) + 1;
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
        $check_date_end = $check_date[1] . "-" . "31";
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
                                                  "field"=>"leave_category",
                                                  "style"=>"equals",
                                                  "data" => array("value" => 'leave_category|'.$leave_cat)))
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
            $start_date = strtotime($leavReqObj->getField("start_date")->getDBValue());
            $end_date = strtotime($leavReqObj->getField("end_date")->getDBValue());
            $days = floor(($end_date-$start_date)/(60*60*24)) + 1;
            $balance = $balance - $days;
            $days_on_leave = $days_on_leave + $days;
          }
        }
      }
      /*
      $template->setDisplayDataImmediate( "leave_balance_counter", ++$index, $row_node );
      $template->setDisplayDataImmediate( "leave_balance_period", $leave_period, $row_node );
      $template->setDisplayDataImmediate( "leave_balance_category", $leaveCatName, $row_node );
      $template->setDisplayDataImmediate( "leave_balance_max_days", $maxDays, $row_node );
      $template->setDisplayDataImmediate( "leave_balance_leave_days", $days_on_leave, $row_node );
      $template->setDisplayDataImmediate( "leave_balance_balance", $balance, $row_node );
*/
      $tr =$this->template->createElement("tr");
      $td =$this->template->createElement("td",array("id"=>"SN".$leave_request));
      $this->template->addTextNode("SN".$leave_request,++$index,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"period".$leave_request));
      $this->template->addTextNode("period".$leave_request,$leave_period,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"category".$leave_request));
      $this->template->addTextNode("category".$leave_request,$leave_category,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"maxdays".$leave_request));
      $this->template->addTextNode("maxdays".$leave_request,$maxDays,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"daysonleave".$leave_request));
      $this->template->addTextNode("daysonleave".$leave_request,$days_on_leave,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"balance".$leave_request));
      $this->template->addTextNode("balance".$leave_request,$balance,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"approve".$leave_request,"style"=>"text-align:center"));
      $radio =$this->template->createElement("input",array("type"=>"radio","name"=>"action[".$leave_request."]"));
      $this->template->appendNode($radio,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);

      $td =$this->template->createElement("td",array("id"=>"deny".$leave_request,"style"=>"text-align:center"));
      $radio =$this->template->createElement("input",array("type"=>"radio","name"=>"action[".$leave_request."]"));
      $this->template->appendNode($radio,$td);
      $this->template->appendNode($td,$tr);
			$this->template->appendNode($tr,$leave_applicants_node);
    }

  }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-ofactoryset: 4
# End:
