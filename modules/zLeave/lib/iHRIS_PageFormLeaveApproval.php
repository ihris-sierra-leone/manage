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
* @author Carl Leitner <litlfred@ibiblio.org>
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


class iHRIS_PageFormLeaveApproval extends iHRIS_PageFormParentPerson{



    /**
     * Create and load data for the objects used for this form.
     *
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
      if ($this->isPost()) {
        $leave_request = $this->factory->createContainer("leave_request|0");
        $leave_request->load($this->post);
        $primary = $this->factory->createContainer($this->getForm());
        if (!$primary instanceof I2CE_Form) {
            I2CE::raiseError("Could not create leave_request form");
            return;
        }
        $primary->load($this->post);
        $primary->populate();
      }
      else if ($this->get_exists('leave_request')) {
        if ($this->get_exists('leave_request')) {
            $leave_request_id = $this->get('leave_request');
            if (strpos($leave_request_id,'|')=== false) {
                I2CE::raiseError("Deprecated use of id variable");
                $leave_request_id = $this->getForm() . '|' . $leave_request_id;
            }
        } else {
            $leave_request_id = "leave_request" . '|0';
        }
        $leave_request = $this->factory->createContainer($leave_request_id);
        $leave_request->populate();
        $parent = $this->get('parent');

        $where = array( "operator"=>"FIELD_LIMIT",
                        "field"=>"leave_request",
                        "style"=>"equals",
                        "data"=>array("value"=>$leave_request_id)
                      );
        $find_approval = I2CE_FormStorage::search("leave_approval",false,$where);
        if(count($find_approval) == 1) {
          $primary = $this->factory->createContainer($this->getForm() . "|" . $find_approval[0]);
        }
        else if(count($find_approval) > 1) {
          I2CE::raiseError("More than one leave_approval form found with IDs " . print_r($find_approval,true));
          return false;
        }
        else
        $primary = $this->factory->createContainer($this->getForm());
        $primary->populate();
        $primary->setParent($parent);
        if (!$leave_request instanceof I2CE_Form) {
          I2CE::raiseError("Could not create valid leave_request form from id:$leave_request_id");
          return false;
        }
      }
      $person = $this->factory->createContainer( $primary->getParent());
      if (!$person instanceof I2CE_Form) {
          return;
      }
      $person->populate();
      
      $role=$this->getUser()->role;
      $username=$this->getUser()->username;
      $where = array("operator"=>"FIELD_LIMIT",
                     "field"=>"username",
                     "style"=>"equals",
                     "data"=>array("value"=>"user|".$username)
                   );
      $user_map = I2CE_FormStorage::listFields("user_map",array("parent"),false,$where);
      if(count($user_map) == 0 && $role == 'hod') {
        $this->userMessage("We cant locate your department,assign yourself a department first");
        $this->setRedirect("home");
      }
      else if(count($user_map) == 1) {
        $parent = current($user_map);
        $user_person_id = $parent["parent"];
      }

      $user_dep = $this->getDep($user_person_id);
      $empl_dep = $this->getDep("person|".$person->getId());
      $is_hod = false;
      $is_hr = false;
      if ($user_dep == $empl_dep && $role == 'hod') {
        $is_hod = true;
      }
      if ($role == 'hr_manager') {
        $is_hr = true;
      }
      if(!$is_hr and !$is_hod) {
          $this->userMessage("Only HOD or HR Manager can approve leave request");
          $this->setRedirect("home");
      }
      if ($this->isPost()) {
        $primary->getField("leave_request")->setFromDB($leave_request->getField("id")->getDBValue());
        $leave_request->populate();
        $hr_approval = $leave_request->getField("hod_approval")->getDBValue();
        if($hr_approval !== 0 && $hr_approval !==1 && $is_hr) {
          $this->userMessage("Wait for HOD to process this request before you can process it");
          $this->setRedirect("home");
        }
        if($primary->getField("approved")->getDBValue() == 1) {
          if($is_hr) {
            $leave_request->getField("hr_approval")->setValue("1");
            $leave_request->getField("leave_status")->setFromDB("leave_status|approved");
          }
          else if ($is_hod) {
            $leave_request->getField("hod_approval")->setValue("1");
            $leave_request->getField("leave_status")->setFromDB("leave_status|waiting_hr_approval");
          }
        }
        else if($primary->getField("approved")->getDBValue() == 0) {
          if($is_hr) {
            $leave_request->getField("hr_approval")->setValue("1");
          }
          else if ($is_hod) {
            $leave_request->getField("hod_approval")->setValue("1");
          }
          $leave_request->getField("leave_status")->setFromDB("leave_status|denied");
        }
        $leave_request->getField("approved_return_date")->setFromDB($leave_request->getField("return_date")->getDBValue());
      }
      
      $this->setObject($person, self::EDIT_PARENT);
      $this->setObject($primary, self::EDIT_PRIMARY);
      $this->setObject($leave_request, self::EDIT_SECONDARY);
    }

    public function getDep($person_id) {
      if($person_id == '') {
        return null;
      }
      $ff = I2CE_FormFactory::instance();
      $persObj = $ff->createContainer($person_id);
      $persObj->populateChildren("person_position");
      if(count($persObj) == 0)
      return ;
      foreach($persObj->getChildren("person_position") as $reviewedPersPosObj) {
        $end_date = $reviewedPersPosObj->getField("end_date")->getDisplayValue();
        if($end_date != "")
        continue;
        //getting position of the person being reviewed
        $position = $reviewedPersPosObj->getField("position")->getDBValue();
        $posObj = $ff->createContainer($position);
        if(!$posObj instanceof iHRIS_Position) {
          I2CE::raiseError("$position Not instance of iHRIS_Position");
          return;
        }
        $posObj->populate();
        $dep = $posObj->getField("department")->getDBValue();
        return $dep;
      }
    }

    /**
     * Save the objects to the database.
     *
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        parent::save();
        $this->userMessage("Leave Approved Successfully");
        $primary = $this->factory->createContainer($this->getForm());
        $primary->load($this->post);
        $this->setRedirect(  "view?id=" . $primary->getParent() );
        //$leave_request->cleanup();
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
