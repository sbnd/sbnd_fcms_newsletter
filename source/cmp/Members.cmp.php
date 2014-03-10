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
BASIC::init()->imported("Profiles.cmp", "cms/controlers/back");

class Members extends Profiles {
	
	function main(){

		parent::main();
		
		$this->setField('unsubscribed', array(
				'formtype' => 'none',
				'dbtype' => 'int',
				'defult' => 0
		));
		
		$this->setField('date_unsubscribed', array(
				'formtype' => 'none',
				'dbtype' => 'int'
		));
	}
	
}