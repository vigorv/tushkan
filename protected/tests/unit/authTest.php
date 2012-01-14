<?php
class authTest extends CTestCase
{
	function testSaveToSession()
	{
		ob_start();
		$_identity = new UserIdentity('test', 'test');
		$this->assertTrue((boolean)$_identity);

		$blankInfo = array(
			'id' 				=> 1,
			'email' 			=> 'test@tushkan',
			'name'				=> 'test',
			'salt'				=> 'test',
			'password_original'	=> 'test',
			'modified'			=> time(),
		);
		$blankInfo['pwd'] = $_identity->transformPassword($blankInfo);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_identity->saveAuthInfo($blankInfo);
		$check = $_identity->checkAuthInfo($blankInfo);
		$this->assertTrue((boolean)$check);

//		$this->assertEqua
ls('<strong>test</strong>',
		ob_end_flush();
	}
}