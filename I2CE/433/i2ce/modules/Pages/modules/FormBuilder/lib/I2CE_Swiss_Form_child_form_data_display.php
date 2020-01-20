<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @package i2ce
* @subpackage form-builder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_MAPPED_orders
* 
* @access public
*/


class I2CE_Swiss_Form_child_form_data_display extends I2CE_Swiss_MAPPED_forms_menu {
    

    protected function getAllowedForms() {
        if ($this->parent instanceof I2CE_Swiss_Form_child_form_data_displays
            && ($gp = $this->parent->parent) instanceof I2CE_Swiss_Form_meta) {
            return $gp->getSelectedForms();

        }
        return array();
    }

    

    protected function getChildType($child) {
	return 'Form_child_form_data';
    }

    protected function getContainerTemplate() {
        return 'swiss_child_form_data_display.html';
    }

    protected function getAjaxContainer() {
        return  'container';
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
