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

/**
 * newsletter
 *
 * @author Tsvetan Ignatov
 * @version 0.1 [09-03-2012]
 * @package BASIC.SBND.NEWSLETTER
 */
 
BASIC::init()->imported('upload.mod');

class SendingEmails extends CmsComponent {
	public $max_file_size      = '5M';
	public $support_file_types = 'jpg,jpeg,gif,png,html,htm,doc,docx,xlsx,rar,zip,ppt,pdf,txt';
	public $upload_folder 	   = 'upload';
	   	  
	
	public function main(){
		
		parent::main();
		$this->template_form='cmp-emailform.tpl';
		
		$this->setField('send_to_all', array(
            'formtype' => 'radio',
            'dbtype' => 'int',
            'default' => '0',
            'attributes' => array(
            	'data' => array(
        			BASIC_LANGUAGE::init()->get('yes'),
        			BASIC_LANGUAGE::init()->get('no')
        		)
            )
        ));	
		$this->setField('user_groups', array(
			'name' => BASIC_LANGUAGE::init()->get('user_groups'),
			'formtype' => 'multiple',
			'dbtype' => 'none',
			'messages' => array(
				1 => BASIC_LANGUAGE::init()->get('email_members_isempty')
			),
			'attributes' => array(
				'onchange' => 'SendingMail.groups(this)',
				'style' => 'height:100px;'
			)
		));
		 
		$this->setField('search_users', array(
			'name' => BASIC_LANGUAGE::init()->get('search_groups'),
			'dbtype' => 'none',
			'attributes' => array(
				'onkeyup' => 'SendingMail.users(this)'
			),
			'messages' => array(
				1 => BASIC_LANGUAGE::init()->get('email_members_isempty')
			)
		));
		
		$this->setField('select_users', array(
            'formtype' => 'selectmove',
            'dbtype' => 'text',
            'length' => '255'
           
         ));
        $this->setField('email_template', array(
            'formtype' => 'select',
            'default' => 0,
            'attributes' => array(
        		'onchange' => 'ajaxRunner(this)' ,      
        	)
        ));        
		$this->setField('subject',array(
			'text' => 'subject',
			'filter' => 'auto',
					
		));

		$this->setField('file', array(
 		    'text' => BASIC_LANGUAGE::init()->get('image'),
   		    'formtype' => 'file',
			'messages' 	=> array(
				
				2  => BASIC_LANGUAGE::init()->get('upoad_error_2'),
				3  => BASIC_LANGUAGE::init()->get('upoad_error_3'),
				4  => BASIC_LANGUAGE::init()->get('upoad_error_4'),
				10 => BASIC_LANGUAGE::init()->get('upoad_error_10'),
				11 => BASIC_LANGUAGE::init()->get('upoad_error_11'),
				12 => BASIC_LANGUAGE::init()->get('upoad_error_12'),
				13 => BASIC_LANGUAGE::init()->get('upoad_error_13'),
				14 => BASIC_LANGUAGE::init()->get('upoad_error_14'),
				15 => BASIC_LANGUAGE::init()->get('upoad_error_15'),
				16 => BASIC_LANGUAGE::init()->get('upoad_error_16'),
			),   		    
   		    'attributes' => array(
				'max' 	 		=> $this->max_file_size,
				'rand'   		=> 'true', 
				'as' 	 		=> 'ART', 
				'preview' 		=> '200,200',   
				'dir' 	 		=> $this->upload_folder,
				'perm' 	 		=> $this->support_file_types,
				'delete_btn' 	=> array(
					'text' => BASIC_LANGUAGE::init()->get('delete'),
  		 		 )
  		 	)
  		));
  		$this->setField('message', array( 
			'text' => BASIC_LANGUAGE::init()->get('message'),
			'formtype' => 'html',
			'dbtype' => 'longtext'	
		));
			
		//user_types		
		$this->user_types = array(
				'members' => 'members'
		);
		
		//available users array
		$this->available_users = array(
				$this->user_types['members'] => array()
		);
		
		$this->updateAction('list', 'ActionFormAdd');
		$this->updateAction('save', 'ActionSave', BASIC_LANGUAGE::init()->get('Send_mail'));
		$this->addAction('emailtemplates', 'goToChild', BASIC_LANGUAGE::init()->get('email_templates'),3);
		$this->errorAction = 'add';
	}
	
	
	function startPanel() {
		
		//get profiles
		$rdr = Builder::init()->build('profiles', false)->read(' AND `unsubscribed` = 0 AND `active` = 1 ');
		while($rdr->read()){
			if($rdr->item('email')) {
				$this->available_users['members'][$rdr->item('email')] = $rdr->getItems();
			}
		}
		
		return parent::startPanel();
	}
	
	
function ActionFormAdd(){
		
		$this->updateField(('email_template') , array(
		   'attributes' => array(
		    'data'=>Builder::init()->build('emailtemplates')->read()->getSelectData()
		   ) 
		 ));
		
		//get profiles
		$profiles_groups = Builder::init()->build('profiles-types')->read('AND `id` != -2 ')->getSelectData();
		
		$this->updateField(('user_groups') , array(
			'attributes' => array(
				'data' => $profiles_groups
			)	
		));
		
		$users = '';
		
		//build data for the selectboxes	
		//add profiles
		if (!empty($this->available_users['members'])) {
			foreach($this->available_users['members'] as $member) {
				if($users) $users .= ','."\n";
				$name = $member['name'];
				$users .= '{email:"'.$member['email'].'", name:"'.$name.'", group: "'.$member['level'].'"}';
			}
		}
				
		BASIC_GENERATOR::init()->script('
			var SendingMail = (function (){
	
				// private space ...
				
				var timer = -1;
				var tmp_users = [];
				var tmp_arr = [];
				var tmp_src;
				// zarejdat se pri otvarqne na formata
				var users =[
					'.$users.'
				];
					return {	
					 groups: function (obj){
						var multiselected = {};  
						tmp_users = [];
						  			
			    		$("#user_groups :selected").each(function(){
			        		multiselected[$(this).val()] = $(this).val();	
			        	});
			        	//$("#select_users_all").get(0).reset(); 
			        		
			    		for(var p in users){
    						 var item = users[p];
							
							if(multiselected[item.group]){
								tmp_users.push({
									value : item.email, 
									text: item.name
								});
						    }
         				}     				
         				$("#select_users").get(0).reset(tmp_users);
					},
					users: function (obj){
						clearTimeout(timer);
						timer = setTimeout(function (){
							var tmp = tmp_users,
								tmp_move = [];
								
								if (!tmp.length) {
									tmp = users;	
								} 
								for (var q in tmp){
									var name = tmp[q].text || tmp[q].name;
									if (name.toLowerCase().indexOf(obj.value.toLowerCase())!=-1){
										tmp_move.push({
											value: tmp[q].email ||  tmp[q].value,
											text : name
										});
									}
								}
								$("#select_users").get(0).reset(tmp_move);					
						}, 300);
														
					},				
				}
			})();
		', array('head' => true));	
		if(BASIC_URL::init()->cookie('contact_sended')){
			BASIC_URL::init()->un('contact_sended');
			
			BASIC_ERROR::init()->setMessage(BASIC_LANGUAGE::init()->get('contact_send_success'));
		}
		
		return parent::ActionFormAdd();
	}
	
	function ActionSave(){
		BASIC::init()->imported('spam.mod');
	
		$email_body = '';
		
		if ($this->getDataBuffer('email_template') != ''){
			$template_content = Builder::init()->build('emailtemplates')->getRecord((int)$this->getDataBuffer('email_template'));
			$email_body = $template_content['content'];
		}
		else{
			$email_body = $this->getDataBuffer('message');
		}
		
		$profiles_groups = Builder::init()->build('profiles-types')->read('AND `id` != -2 ')->getSelectData();
		if($this->getDataBuffer('send_to_all') == 0){
			foreach($this->available_users['members'] as $member){
				$selected_users[] = $member['email'];
			}
		}else{
			$selected_users = $this->getDataBuffer('select_users');
		}
		$newsletter_members_data = array();
		
		if (!empty($selected_users)) {
			$send_email_body = '';
			//preapre mail data
			
			$unsubscribe_text = BASIC_LANGUAGE::init()->get('newsletter_unsubscribe_text');

			$pages_controller = Builder::init()->build('pages');
			
			$unsubscribe_url = $pages_controller->getPageTreeByName('unsubscribe-page');
			
			$unsubscribe_url = BASIC_URL::init()->link('/en/'.$unsubscribe_url);
			
			$mail_obj = new BasicMail(CMS_SETTINGS::init()->get('SITE_EMAIL'), CMS_SETTINGS::init()->get('SITE_NAME'), array(
					'subject' => $this->getDataBuffer('subject')
			));
			
			if($this->getDataBuffer('file')->tmpName){
				$mail_obj->attach($this->getDataBuffer('file')->tmpName, $this->getDataBuffer('file')->fullName, null, $this->getDataBuffer('file')->type);
			}
			
			$newsletter_management_obj = Builder::init()->build('newsletter-members-data');
			 
			$rdr = $newsletter_management_obj->read();
			while($rdr->read()) {
				$key = $rdr->item('user_id')."_".$rdr->item('user_type');
				$newsletter_members_data[$key] = $rdr->getItems();
			}
			//foreach all users
			foreach ($selected_users as $mail) {
				
				//generate code				
				$user_code = md5(uniqid()." ".$mail);
				$user_type = '';
				$user_id = 0;
				$newsletter_management_buffer = array();
				
				//get user data and save into newsletter table
				foreach ($this->user_types as $type) {
					if (isset($this->available_users[$type][$mail]) && isset($this->available_users[$type][$mail]['id'])) {
						$user_type = $type;
						$user_id = $this->available_users[$type][$mail]['id'];
						$key = $user_id."_".$user_type;

						//if there is no such data into table set buffer
						if (!isset($newsletter_members_data[$key])) {
							//one email can exists in all members types
							$newsletter_management_buffer[] = array('code' => $user_code, 'user_type' => $user_type, 'user_id' => $user_id);
							
						} else { //else get the unsubscribe code
							$user_code = $newsletter_members_data[$key]['code'];
						}
					}
				}
				
				//if there are new users added
				if (!empty($newsletter_management_buffer)) {
					
					//one email can exists in all members types
					foreach ($newsletter_management_buffer as $buffer) {
						$newsletter_management_obj->setDataBuffer('code', $buffer['code']);
						$newsletter_management_obj->setDataBuffer('user_type', $buffer['user_type']);
						$newsletter_management_obj->setDataBuffer('user_id', $buffer['user_id']);
						$newsletter_management_obj->setDataBuffer('unsubscribed_reason', '');
						$newsletter_management_obj->ActionSave(0);
						$newsletter_management_obj->cleanBuffer();
					}
					
				}
				
				$unsubscribe_url .= "code/".$user_code;
				$send_email_body .= $email_body.sprintf("%s <a href='%s'>here</a>", $unsubscribe_text, $unsubscribe_url);
				$mail_obj->body($send_email_body);
				if($mail_obj->send($mail)) {
					BASIC_ERROR::init()->setMessage(BASIC_LANGUAGE::init()->get('contact_send_fail'));
					return false;
				} else {
					BASIC_URL::init()->set('contact_sended', 1, 'cookie');
				}
				
			}
			
		}
		
	}

	
	function settingsData(){
		return array(
			'base' 			=> $this->base,
			'max_file_size'	=> $this->max_file_size,
			'support_file_types' => $this->support_file_types,
			'upload_folder' 	=> $this->upload_folder
		);
	}
	
	function settingsUI(){
		return array(
			'base' => array(
				'text' => BASIC_LANGUAGE::init()->get('db_table')	
			),
			'max_file_size' => array(
				'text' => BASIC_LANGUAGE::init()->get('max_file_size'),
			),
			
			'support_file_types' => array(
				'text' => BASIC_LANGUAGE::init()->get('support_file_types'),
			),
			'upload_folder' => array(
				'text' => BASIC_LANGUAGE::init()->get('upload_folder'),
			),
		);
	}
}