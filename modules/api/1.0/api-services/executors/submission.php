<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018



require_once $GLOBALS['__PACKAGES__DIR__'].'/aido_dashboard/AIDODashboard.php';
use \system\packages\aido_dashboard\AIDODashboard;
use \system\classes\Core;

require_once $GLOBALS['__SYSTEM__DIR__'].'/api/1.0/utils/utils.php';

function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'retire':
			$user_id = Core::getUserLogged('username');
			// get arguments
			$subm_id = $arguments['submission_id'];
			// retire submission
			$res = AIDODashboard::retireSubmission( $subm_id, $user_id );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK();
			break;
		//
		default:
			return response404NotFound( sprintf("The command '%s' was not found", $actionName) );
			break;
	}
}//execute

?>
