<?php
$res = Builder::init()->build('emailtemplates')->getRecord((int)BASIC_URL::init()->request('id'));
die(json_encode($res));