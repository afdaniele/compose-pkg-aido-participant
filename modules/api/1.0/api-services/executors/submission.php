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
		case 'list':
			// get username of the current user
			$user_id = Core::getUserLogged('username');
			// get arguments
			$filter_status = null;
			if( isset($arguments['status']) ) $filter_status = $arguments['status'];
			$recent_first = true;
			if( isset($arguments['recent_first']) ) $recent_first = boolval($arguments['recent_first']);
			// get the list of submissions created by the user
			$res = AIDODashboard::getUserSubmissions( $user_id, $filter_status, null, null, $recent_first );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			// remove `user` info from submissions
			foreach( $res['data']['page_data'] as &$subm ){
				unset( $subm['user'] );
			}
			//
			return response200OK([
				'submissions' => $res['data']['page_data']
			]);
			break;
		//
		case 'status':
			// get username of the current user
			$user_id = Core::getUserLogged('username');
			// get arguments
			$subm_id = $arguments['id'];
			// get submission
			$res = AIDODashboard::getSubmission( $user_id, $subm_id );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK([
				'id' => $subm_id,
				'label' => $res['data']['label'],
				'status' => $res['data']['status']
			]);
			break;
		//
		case 'create':
			// get username of the current user
			$user_id = Core::getUserLogged('username');
			// get arguments
			$subm_label = $arguments['label'];
			$subm_content = $arguments['content'];
			// create submission
			$res = AIDODashboard::createSubmission( $user_id, $subm_label, $subm_content );
			if( !$res['success'] ){
				return response400BadRequest( $res['data'] );
			}
			//
			return response200OK([
				'id' => $res['data']
			]);
			break;
		//
		case 'delete':
			// get username of the current user
			$user_id = Core::getUserLogged('username');
			// get arguments
			$subm_id = $arguments['id'];
			// create submission
			$res = AIDODashboard::deleteSubmission( $user_id, $subm_id );
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
