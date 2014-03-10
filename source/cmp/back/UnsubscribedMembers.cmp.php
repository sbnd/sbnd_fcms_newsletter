<?php
/**
* SBND F&CMS - Framework & CMS for PHP developers
*
* Copyright (C) 1999 - 2013, SBND Technologies Ltd, Sofia, info@sbnd.net, http://sbnd.net
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author SBND Techologies Ltd <info@sbnd.net>
* @package cms.controlers.back
* @version 7.0.6
*/

class UnsubscribedMembers extends CmsComponent{
	
	function main(){
		parent::main();
		
		$this->setField('name', array(
			'text' => BASIC_LANGUAGE::init()->get('contact_user_name')
		));
		
		$this->setField('email', array(
			'text' => BASIC_LANGUAGE::init()->get('user_email')
		));
		
		$this->setField('user_type', array(
				'text' => BASIC_LANGUAGE::init()->get('user_type')
		));
		
		$this->setField('unsubscribed_reason', array(
				'text' => BASIC_LANGUAGE::init()->get('unsubscribed_reason')
		));
		
		$this->setField('date_unsubscribed', array(
			'text' => BASIC_LANGUAGE::init()->get('date'),
			'formtype' => 'date',
			'attributes' => array(
					'format' => '%Y-%m-%d',
					'dataformat' => 'str'
			),
		));
		
		
		$this->delAllActions();
	}

	/** 
	 * @see DysplayComponent::compile()
	 */
	function compile($arr) {
			$arr = array();
			
			$management_obj = Builder::init()->build('newsletter-members-data');
			
			$criteria = sprintf(" AND `unsubscribed` = 1");
			
			$id = 1;
			
			//get profiles
			$members = Builder::init()->build('profiles', false);
			$rdr = $members->read($criteria);
			
			while($rdr->read()) {
				$management_data = $management_obj->read(' AND `user_id` = '.$rdr->item('id').' AND `user_type` = "members" LIMIT 1')->getArrayData();
				
				$reason = (!isset($management_data[0]['unsubscribed_reason'])) ? '' : $management_data[0]['unsubscribed_reason'];
				
				$arr[] = array (
							'id' => $id, 
							'name' => $rdr->item('name'), 
							'email' => $rdr->item('email'), 
							'unsubscribed_reason' => $reason, 
							'date_unsubscribed' => $rdr->item('date_unsubscribed'),
							'user_type' => 'members'
				);
				$id++;
			}
			
		return parent::compile($arr);
	}
	
	function ActionFormAdd(){

		return parent::ActionFormAdd();
	}
	
	function ActionFormEdit($id){
		return parent::ActionFormEdit($id);
	}
	function ActionList(){
				
		$this->map('name', BASIC_LANGUAGE::init()->get('name'), 'formatter');
		$this->map('email', BASIC_LANGUAGE::init()->get('email'), 'formatter');
		$this->map('user_type', BASIC_LANGUAGE::init()->get('type'), 'formatter');
		$this->map('unsubscribed_reason', BASIC_LANGUAGE::init()->get('unsubscribed_reason'), 'formatter');
		$this->map('date_unsubscribed', BASIC_LANGUAGE::init()->get('date_unsubscribed'), 'formatter');

		return parent::ActionList();
	}
	function formatter($val, $name, $row){
		
		if ($name=='date_unsubscribed') {
			
			$val = date('Y-m-d H:i:s', $val);
			
		}
		
		return $val;
	}
	
}