<?php 
/**
* SBND F&CMS - Framework & CMS for PHP developers
*
* Copyright (C) 1999 - 2014, SBND Technologies Ltd, Sofia, info@sbnd.net, http://sbnd.net
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
* @version 7.0.6
*/

/**
 * NewsLetterUsersManagement
 *
 * @version 2.0
 * @package BASIC.SBND.NEWSLETTER
 */
 
class NewsLetterUsersManagement extends CmsComponent  {
	
	public $template_form = 'unsubscribe.tpl';
	public $base = 'newsletter_members_data';
	public $unsubscribe_reasons = array();
	
	function main() {
		
		parent::main();
		
		$this->setField('code', array(
				'formtype' => 'hidden',
				'length' => '32'
		));
		
		$this->setField('user_id', array(
				'formtype' => 'none',
				'dbtype' => 'int',
				'perm' => '*'
		));
		
		$this->setField('user_type', array(
				'formtype' => 'none',
				'perm' => '*'
		));
		
		
		$this->unsubscribe_reasons = array(
			0 => BASIC_LANGUAGE::init()->get('unsubscribe_not_interested'),
			1 => BASIC_LANGUAGE::init()->get('unsubscribe_sent_often'),
			2 => BASIC_LANGUAGE::init()->get('unsubscribe_other_reason')
		);
		
		$this->other_reason_key = 2;
		
		$this->setField('unsubscribed_reason', array(
				'text' => BASIC_LANGUAGE::init()->get('reason'),
				'perm' => '*',
				'filter' => 'auto',
				'formtype' => 'select',
				'attributes' => array(
					'data' => $this->unsubscribe_reasons,
					'onchange' => 'ReasonManager.checkReason(this)'
				),
				'messages' => array(
						1 => BASIC_LANGUAGE::init()->get('is_required')
				)
		));
		
		$this->setField('other_reason', array(
				'text' => BASIC_LANGUAGE::init()->get('unsubscribe_other_reason'),
				'formtype' => 'textarea',
				'attributes' => array(
					'id' => 'other_reason',
					'class' => 'hiddenField'
				)
		));
		
		$this->specialTest = 'validate';
		$this->prefix = 'unsubscribe_form';
		
	}
	
	/**
	 * 
	 * Call actions and return generated from component html
	 * 
	 * @access public
	 * @return string
	 */
	function startPanel(){
		$code = '';
		$user_data = array();
		
		//if there is a code
		if ($code = BASIC_URL::init()->request('code', 'clearUpInjection')) {
			
			//first check code and user existence
			$criteria = sprintf(" AND `code` = '%s' ", $code);
			
			$user_data = $this->read($criteria)->getArrayData();
			
			if (empty($user_data)) BASIC_URL::init()->redirect(BASIC::init()->virtual());
			
			$this->delAllActions();
			
			$this->addAction('unsubscribe', 'ActionUnsubscribe', BASIC_LANGUAGE::init()->get('unsubscribe'), 3);
			$this->updateAction('list', 'ActionFormAdd');
			$this->addAction('error', 'ActionFormAdd');
			
		} else {
			
			//code not exists -> go to homepage
			BASIC_URL::init()->redirect(BASIC::init()->virtual());
			
		}
		
		return parent::startPanel();
	}
	
	function ActionFormAdd() {
		
		if (empty($this->messages['other_reason'])) {
			BASIC_GENERATOR::init()->script('
					$(function() {
						ReasonManager.hideHiddenFieldsBoxes();
					})		
			', array('head' => true));
		}
			
		
		BASIC_GENERATOR::init()->script('
				
			var ReasonManager = {
				checkReason: function(el) {
				
					//if the reason is other reason
					if ($(el).val() == '.$this->other_reason_key.') {
						$("#'.$this->prefix.' .hiddenField").closest("div").show();
					} else {
						$("#'.$this->prefix.' .hiddenField").closest("div").hide();
					}
				},
				hideHiddenFieldsBoxes: function() {
					$("#'.$this->prefix.' .hiddenField").closest("div").hide();
				}
			}			
			
			
		', array('head' => true));
		
		
		return parent::ActionFormAdd();
	}
	
	function validate() {
				
		$err = false;
		
		//if other reason is selected , check for the reason text field
		if($this->dataBuffer['unsubscribed_reason'] == $this->other_reason_key) {
			
			if (empty($this->dataBuffer['other_reason'])) $err = $this->setMessage('other_reason',1);
			
		} else if (!isset($this->unsubscribe_reasons[$this->dataBuffer['unsubscribed_reason']])) {
			$err = $this->setMessage('unsubscribed_reason',1);
		}
		
		$code = BASIC_URL::init()->request('code', 'clearUpInjection');
		
		if (!$code) {
			
			//code not exists -> go to homepage
			BASIC_URL::init()->redirect(BASIC::init()->virtual());
			
		}
		
		return $err;
		
	}
	
	function ActionFormEdit($id) {
		
		return $this->ActionFormAdd();
	}
	
	function ActionUnsubscribe() {
		
		//set the code
		$code = BASIC_URL::init()->request('code', 'clearUpInjection');
		
		$reason = '';
		
		//set the reason
		if (isset($this->dataBuffer['other_reason'])) {
			$reason = ($this->dataBuffer['unsubscribed_reason'] == $this->other_reason_key) ? $this->dataBuffer['other_reason'] : $this->unsubscribe_reasons[$this->dataBuffer['unsubscribed_reason']]; 		
		}
		
		//first get user data
		$criteria = sprintf(" AND `code` = '%s' ", $code);

		$user_data = $this->read($criteria)->getArrayData();
		
		//do the unsubscribtion
		$this->unsubscribeUser($user_data, $reason);
		
		BASIC_TEMPLATE2::init()->set('unsubscribe_text', BASIC_LANGUAGE::init()->get('unsubscribe_success', $this->template_form));
		
		//code not exists -> go to homepage
		BASIC_URL::init()->redirect(BASIC::init()->virtual());
		
		return true;
	}
	
	/**
	 * 
	 * Function for unsubscribe the user from all tables depends on 
	 * 
	 * @param unknown $user_data
	 */
	private function unsubscribeUser($user_data, $reason) {
		
		if (!empty($user_data)) {
			foreach ($user_data as $user) {
				$obj = null;
				
				switch ($user['user_type']) {
					case 'members' :
						$obj = Builder::init()->build('profiles', false);
					break;
				}
				
				$obj->setBuffer(array('unsubscribed' => 1, 'date_unsubscribed' => time()));
				$obj->ActionSave($user['user_id']);
				$obj->cleanBuffer();
				
				//clear the buffer
				$this->cleanBuffer();
				
				//set the unsubscribe reason
				$this->setDataBuffer('unsubscribed_reason', $reason);
				
				//do the save
				parent::ActionSave($user['id']);
			}
			
		}
		
		
	}
	
	function checkSubscriberExistence($email) {
		
		$subscriber_id = 0;
		
		$criteria = sprintf(' AND `email` = "%s" LIMIT 1', $email);
		
		$subscriber_id = $this->read($criteria)->getArrayData();
		
		return $subscriber_id;
		
	}
	
	function settingsData(){
		return array(
		);
	}
	
	function settingsUI(){
		return array(
					
		);
	}
		
}