<?php
/**
 * Email Templates
 *
 * @author Tsvetan Ignatov
 * @version 0.1 [09-03-2012]
 * @package BASIC.SBND.NEWSLETTER
 */
class EmailTemplates extends CmsComponent{

    public $base			   = 'emailtemplates';
	function main(){
		parent::main();
		
		$this->setField('title', array(
			'text' => BASIC_LANGUAGE::init()->get('title')
		));
		$this->setField('content', array( 
			'text' => BASIC_LANGUAGE::init()->get('content'),
			'formtype' => 'html',
			'dbtype' => 'longtext'
						
		));
		$this->ordering(true);
	}
	function ActionList(){ 
		$this->map('title', BASIC_LANGUAGE::init()->get('title'), '', 'align=left');
		
		return parent::ActionList();
	}
	function settingsData(){
		return array(
			'base' 			=> $this->base			
		);
	}
	
	function settingsUI(){
		return array(
			'base' => array(
				'text' => BASIC_LANGUAGE::init()->get('db_table')	
			),

		);
	}
	
	
}