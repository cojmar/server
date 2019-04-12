<?php
/*
	Nginx RTMP authentitaction PHP class and handler example v 1.0	
	Last update 2016-07-01
	Made by Alex Platon aka Cojmar - 5AM script :P
*/

//die(new rtmp_auth()); //== no handler example (allow all)
//==Init class with Handler
new rtmp_auth(function($data)
{
	//==Restrictions config
	$restrictions = array
	(
		'deny_all_non_restricted'	=> false, //==> Values: true, false
		//==Restrict app private 
		'private'	=> array
		(
			//==To this channels only: cam1, cam2
			'cam1'	=> true,		//== dosen't need a loging method
			'cam2'	=> array	//== if $data has any of this sets of params with exact values auth ok else fail
			(
				//==Allow any viwer with pass 'myviewpass'
				array
				(
					'call'		=>'play',
					'pass'	=>'myviewpass',
				),
				//==Allow publisher with user 'mypublishuser' and pass 'mypublishpass'
				array
				(
					'call'		=>'publish',
					'user'	=>'mypublishuser',
					'pass'	=>'mypublishpass',
				),
			),
		),
	);
	//==Apply restrictions
	if (isset($restrictions[$data['app']]))
	{
		$app = $restrictions[$data['app']];
		if (is_array($app))
		{
			if (!empty($app[$data['name']]))
			{
				file_put_contents('debug.txt',print_r($data,1));
				if (is_array($app[$data['name']]))
				{
					$ok =false;
					foreach($app[$data['name']] as $login_data)
					{
						$user_ok = true;
						foreach ($login_data as $k=>$v)
						{
							$vv = (isset($data[$k]))?$data[$k]:null;
							if ($vv != $v) $user_ok = false;
						}
						if ($user_ok) $ok = true;
					}
					return $ok;
				}
				else return true;
			}
		}
		return false;
	}
	//==If no restrictions applyed and deny_all_non_restricted active deny
	if (!empty($restrictions['deny_all_non_restricted'])) return false;		
});
//===The Classs
class rtmp_auth
{
	//==Constructor
	function __construct($handler=null)
	{
		//==Accepted params and default values for them, ignore if null
		$req_params = array
		(
			//==Received from NGNINX
			'app'			=> '',			//==Application name
			'name'		=> '',			//==Key / channel name
			'call'			=>	'play',	//==Call method can be : play or publish
			'clientid'	=>	'',			//==Client Id - alwys increments
			'pageurl'		=>	'',		//==Client url if browser
			//==Optional params (added like: "?user=&pass=" at key in encoder/player)
			'user'	=>	'',				//==Set NULL to not send this param to handler
			'pass'	=> ''				//==Set NULL to not send this param to handler
		);
		//==Init $this->data and assign handler if any
		$this->data = array
		(
			'handler'	=>$handler,
			'data'		=>array()
		);
		//==Generate $this->data['data'] after $req_params model ignorig keys with NULL values from final array
		foreach ($req_params as $k=>$v)
		{
			if (!empty($_REQUEST[$k])) $v = $_REQUEST[$k];
			if (!is_null($v)) $this->data['data'][$k] = $v;
		}
		//==Run auth
		$this->auth();
	}
	//==Auth method
	private function auth()
	{
		//==If handler exists call handler with $this->data['data']
		if( is_callable( $this->data['handler']))
		{
			//==Get response from handler
			$call = $this->data['handler']($this->data['data']);
			//==if null or true allow, else deny
			if (is_null($call)||$call==true) $this->allow();
			else $this->deny($call);
		}
		//==if no handler allow all
		$this->allow();
	}
	//==Deny method
	private function deny($error=null)
	{
	  header('HTTP/1.0 404 Not Found');
	  die($error);
	}
	//==Allow method
	private function allow()
	{
		die;
	}
}