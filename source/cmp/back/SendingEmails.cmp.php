<?php
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
		$this->setField('message', array( 
			'text' => BASIC_LANGUAGE::init()->get('message'),
			'formtype' => 'html',
			'dbtype' => 'longtext'	
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
		$this->updateAction('list', 'ActionFormAdd');
		$this->updateAction('save', 'ActionSave', BASIC_LANGUAGE::init()->get('Send_mail'));
		$this->addAction('emailtemplates', 'goToChild', BASIC_LANGUAGE::init()->get('email_templates'),3);
		$this->errorAction = 'add';
	}
	function ActionFormAdd(){
		
		$this->updateField(('email_template') , array(
		   'attributes' => array(
		    'data'=>Builder::init()->build('emailtemplates')->read()->getSelectData('id', 'title', array('' => ' '))
		   ) 
		 ));
		
		$this->updateField(('user_groups') , array(
			'attributes' => array(
				'data' => Builder::init()->build('profiles-types')->read('AND `id` != -2 ')->getSelectData()
			)	
		));
		$users = '';
		$rdr = Builder::init()->build('profiles', false)->read();
		while($rdr->read()){
			if($users) $users .= ','."\n";
			$users .= '{email:"'.$rdr->item('email').'", name:"'.$rdr->item('name').'", group: "'.$rdr->item('level').'"}';
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
		
		$mail = new BasicMail(CMS_SETTINGS::init()->get('SITE_EMAIL'), CMS_SETTINGS::init()->get('SITE_NAME'), array(
			'subject' => $this->getDataBuffer('subject'),
			'body' => $email_body
		));
		if($this->getDataBuffer('file')->tmpName){
			$mail->attach($this->getDataBuffer('file')->tmpName, $this->getDataBuffer('file')->fullName, null, $this->getDataBuffer('file')->type);
		}
		$to = $this->getDataBuffer('select_users');
		if(!$to){
			$rdr = Builder::init()->build('profiles', false)->read();
			$to=array();
			while($rdr->read()){
				$to[]=$rdr->item('email');
			}
		}
	if(!$mail->send($to)){
		BASIC_ERROR::init()->setMessage(BASIC_LANGUAGE::init()->get('contact_send_fail'));
		return false;
		}else{
			BASIC_URL::init()->set('contact_sended', 1, 'cookie');
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