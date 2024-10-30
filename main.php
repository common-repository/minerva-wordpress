<?php
/*
Plugin Name: Minerva Wordpress Plugin
Plugin URI: http://minerva.sapiensworks.com/
Description: Adds Minerva capabilities to your blog. Minerva enables you to be a part of a distributed social network, that is a social network formed by anyone who has Minerva functionality. You don't need an account (your blog IS your account) , you can add friends and join any Minerva network you like. And if you don't find anything you like, you can create your own networks ;) . Easy to use and free to use, you can connect to anyone while keeping your independence.
Author: Mike T.
Version: 0.7.1
Author URI: http://minerva.sapiensworks.com/
*/
require_once dirname ( __FILE__ ) . '/core/minerva.php';
require_once dirname ( __FILE__ ) . '/base.php';
require_once dirname ( __FILE__ ) . '/template.php';

class MinervaController {
	
	static function tag($w) {
		$w [] = 'minerva';
		$w [] = 'minerva_goto';
		$w[]='minerva_action';
		$w[]='minerva_param';
		return $w;
	}
	
	static function init() {
		
		//add query var for token and for redirect
		add_filter ( 'query_vars', array ('MinervaController', 'tag' ) );
		
		//add logout action
		add_action ( 'wp_logout', array ('MinervaController', 'logout' ) );
		
		//action for the redirect and auth detection
		$m= new MinervaController();
		
		
		add_action ( 'parse_request', array ($m, 'execute' ) );
		
		register_sidebar_widget('Minerva Panel',array('MinervaTemplate','Panel'));
		
		register_widget_control ('Minerva Panel',array('MinervaTemplate','PanelConfig'));
	
	}
	
	static function admin() {
		add_menu_page ( 'Minerva Admin', 'Minerva', 10, dirname ( __FILE__ ) . '/admin/menu.php' );
		add_submenu_page(dirname ( __FILE__ ) .'/admin/menu.php','Minerva Networks','Networks',10, dirname ( __FILE__ ) .'/admin/menu.php');
		add_submenu_page(dirname ( __FILE__ ) .'/admin/menu.php','My Minerva Networks','My Networks',10, dirname ( __FILE__ ) .'/admin/my_networks.php');
		add_submenu_page(dirname ( __FILE__ ) .'/admin/menu.php','Minerva Friends','Friends',10, dirname ( __FILE__ ) .'/admin/friends.php');
		$m= new MinervaController();
	}
	
	static function logout() {
		$user=wp_get_current_user();
		$tk=  MinervaToken::create();
		$tk->cleanUser($user->ID);
		MinervaCookie::delete();
	}
	
	static function activate() {
		//db stuff
		if (! get_option ( 'minerva_id' )) {
			$otken = sha1 ( uniqid ( wp_salt (), true ) );
			update_option ( 'minerva_id', $otken );
		}
		$m= new MinervaController();
		
		if (!get_option('minerva_widget'))
		{
			$options['title']='Minerva';
			$options['view_friends']=true;
			$options['view_buddies']=true;
			$options['view_mynets']=true;
			$options['view_partofnets']=true;
			update_option('minerva_widget',$options);
		}
		
	
	}
	
	static function login()
	{
		MinervaCookie::delete();
	}
	
	static function deactivate() 
	{
		delete_option('minerva_tokens');
		delete_option('minerva_temp_tokens');
	}
	
	function execute($wp) 
	{	
	if (isset ( $_SERVER ['HTTP_MINERVA_TRANSPORT']) && ($_SERVER['REQUEST_METHOD']=='POST')) {
			$transport= new MinervaTransport();
			$transport->receive ();
			die ();
		}
		
		/* @var $wp WP */
		if (isset ( $wp->query_vars ['minerva'] )) {
			MinervaAuth::Authorize ( $wp->query_vars ['minerva']);
			die();
		} elseif (isset ( $wp->query_vars ['minerva_goto'] )) {
			MinervaAuth::Redirect (urldecode($wp->query_vars ['minerva_goto']));
			die();
		}
		elseif (isset ( $wp->query_vars ['minerva_action'] ))
		{
			if (!MinervaRegistry::getCookie()) return;
			
			$action=$wp->query_vars ['minerva_action']; 
			$param=$wp->query_vars ['minerva_param'];
			$rez=false;
			switch ($action)
			{
				case 'add_friend': $rez= MinervaFriends::addFriend();
								break;
				
				case 'join_network':  $rez= MinervaNetworks::joinNetwork($param);
								break;
				
			}
			
			if ($rez)
			{
				wp_redirect(MinervaRegistry::getUrl());
					die();
			}
			
			}
	
	}
	
	
	private function handleCookie() 
	{
	if (! isset ( $_COOKIE ['minerva'] )) return;
	$ck = MinervaCookie::decrypt ( $_COOKIE ['minerva'] );
	if ($ck) 
	{
		if (MinervaAuth::validateSession ($ck))
			MinervaRegistry::set('cookie', $ck);
			 else {
			//clean session
			MinervaCookie::delete ();
			 }
	}
	
	}
	
	/**
	 * Creates MinervaCookie if current logged-in user doesn't have any
	 *
	 */
	private function handleUser() 
	{
		if (!is_user_logged_in ()) return;
		//cookie
		if (!MinervaRegistry::getCookie()) 
		{
			$tk = MinervaToken::create();
			$token = $tk->generate(MinervaRegistry::getUrl());
			$user = wp_get_current_user ();
			$ck = new MinervaCookie ($user->display_name, MinervaRegistry::getUrl(), $token );
			$ck->set ();
			MinervaRegistry::set('cookie',$ck) ;
		}
	}
	
	function __construct() {
		
		//init register
		MinervaRegistry::set('url', get_bloginfo ( 'wpurl' ));
		
		//dependency injection
		$di=array(
		'friends'=>'MinervaFriendsProvider',
		'token'=>'MinervaTokenProvider',
		'networks'=>'MinervaNetworksProvider'
		);
		MinervaRegistry::set('di',$di);
		MinervaRegistry::set('system_utils',new MinervaSystemUtils());
		$this->handleCookie ();
		
		if (!MinervaRegistry::getSystemUtils()->getToken())
		echo( 'Non-existing token: please re-activate the plugin to generate a new one' );
		$this->handleUser();
	
	}

}
define ( 'MinervaDebug', false );

register_activation_hook ( __FILE__, array ('MinervaController', 'activate' ) );
register_deactivation_hook ( __FILE__, array ('MinervaController', 'deactivate' ) );
add_action ( 'init', array ('MinervaController', 'init' ) );
add_action ( 'admin_menu', array ('MinervaController', 'admin' ) );
add_action('set_auth_cookie',array ('MinervaController', 'login' ));
?>