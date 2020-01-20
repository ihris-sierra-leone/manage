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


class iHRIS_PageFormLeaveRequest extends iHRIS_PageFormParentPerson{



    /**
     * Create and load data for the objects used for this form.
     *
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        $this->factory = I2CE_FormFactory::instance();
        if ($this->isPost()) {
          $primary = $this->factory->createContainer($this->getForm());
          if (!$primary instanceof I2CE_Form) {
              I2CE::raiseError("Could not create leave_request form");
              return;
          }
          $primary->load($this->post);
          $start_date = $primary->getField("start_date")->getDBValue();
          $end_date = $primary->getField("end_date")->getDBValue();
          $holidays = array();
          $leaveCat = $primary->getField('leave_category')->getDBValue();
          $leaveCatObj = $this->factory->createContainer($leaveCat);
          $leaveCatObj->populate();
          $exclude_weekend = $leaveCatObj->getField('exclude_weekend')->getDBValue();
          $days = $this->getWorkingDays($start_date,$end_date,$exclude_weekend);
          //$days = floor(($end_date-$start_date)/(60*60*24)) + 1;
          $primary->getField("leave_days")->setValue($days);
          $primary->getField("leave_status")->setFromDB("leave_status|waiting_hod_approval");
        }
        else if ($this->get_exists('id')) {
          $id = $this->get('id');
          if ($this->get_exists('id')) {
              $id = $this->get('id');
              if (strpos($id,'|')=== false) {
                  I2CE::raiseError("Deprecated use of id variable");
                  $id = $this->getForm() . '|' . $id;
              }
          } else {
              $id = $this->getForm() . '|0';
          }
          $primary = $this->factory->createContainer($id);
          if (!$primary instanceof I2CE_Form || $primary->getName() != $this->getForm()) {
              I2CE::raiseError("Could not create valid " . $this->getForm() . "form from id:$id");
              return false;
          }
          $primary->populate();
        }
        elseif ( $this->get_exists('parent') ) {
          $primary = $this->factory->createContainer($this->getForm());
          if (!$primary instanceof I2CE_Form) {
              return;
          }
          $parent = $this->get('parent');
          if (strpos($parent,'|')=== false) {
              I2CE::raiseError("Deprecated use of parent variable");
              $parent =  'person|' . $parent;
          }
          $primary->setParent($parent);
        }
        if ($this->isGet()) {
            $primary->load($this->get());
        }
				$person = parent::loadPerson(  $primary->getParent() );
				echo $user = $this->check_user("person|".$person->getId());

        if($user != "hr_manager" and $user != "hr_staff") {
					//$this->userMessage("Only Employee can request leave by him/her self check=".$user);
                    $this->userMessage("Only Hr staff can create  employe leave request ");
					$this->setRedirect("home");
        }
        $this->applyLimits($person,$primary);
        $this->setObject( $person, I2CE_PageForm::EDIT_PARENT);
        $this->setObject( $primary, I2CE_PageForm::EDIT_PRIMARY);
		}
		
		public function check_user($reviewed_person) {
			$ff = I2CE_FormFactory::instance();
			print_r($this->getUser());
			$username=$this->getUser()->username;
			$where = array("operator"=>"FIELD_LIMIT",
										 "field"=>"username",
										 "style"=>"equals",
										 "data"=>array("value"=>"user|".$username)
									 );
			$user_map = I2CE_FormStorage::listFields("user_map",array("parent"),false,$where);
			if(count($user_map) == 0) {
				return $this->getUser()->role;
			}
			else if(count($user_map) == 1) {
				$parent = current($user_map);
				$user_person_id = $parent["parent"];
				if($user_person_id == $reviewed_person) {
					//return "employee";
				}
			}
			return $this->getUser()->role;
		}

    public function applyLimits($persObj,$leave_request) {
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
              $leave_category = $leave_request->getField('leave_category');
              $leave_category->setOption(array("meta","limits","default","leave_category"),$where);
      }

    //The function returns the no. of business days between two dates and it skips the holidays
    function getWorkingDays($from,$to,$exclude_weekend){
      if($exclude_weekend) {
				$workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
			}
			else {
				$workingDays = [1, 2, 3, 4, 5,6,7]; # date format = N (1 = Monday, ...)
			}
      $holidayDays = ['*-12-25', '*-01-01', '2013-12-23']; # variable and fixed holidays

      $from = new DateTime($from);
      $to = new DateTime($to);
      $to->modify('+1 day');
      $interval = new DateInterval('P1D');
      $periods = new DatePeriod($from, $interval, $to);

      $days = 0;
      foreach ($periods as $period) {
          if (!in_array($period->format('N'), $workingDays)) continue;
          if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
          if (in_array($period->format('*-m-d'), $holidayDays)) continue;
          $days++;
      }
      return $days;
    }


    /**
     * Save the objects to the database.
     *
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        $leave_request = $this->factory->createContainer("leave_request");
        $leave_request->load($this->post);
        parent::save();
        $this->userMessage("Leave Request Successfully Submitted To Supervisor");
        $this->setRedirect(  "view?id=" . $leave_request->getParent() );
        $leave_request->cleanup();
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
