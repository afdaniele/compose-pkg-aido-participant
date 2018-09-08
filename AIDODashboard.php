<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, July 18th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele



namespace system\packages\aido_dashboard;

use \system\classes\Core;
use \system\classes\Utils;
use \system\classes\Database;
use \system\classes\Configuration;
use \system\packages\aido\AIDO;

/**
*   Module for managing AIDO submissions, challenges, etc.
*/
class AIDODashboard{

	// TODO:remove
	// private static $sql = null;

	private static $initialized = false;

	// disable the constructor
	private function __construct() {}

	/** Initializes the module.
     *
     *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
     */
	public static function init(){
		if( !self::$initialized ){
			// register new user types
			Core::registerNewUserRole('aido', 'candidate');
			Core::registerNewUserRole('aido', 'participant', 'dashboard');
			// set the user role to be an `aido:candidate` (by default)
			Core::setUserRole('candidate', 'aido');
			// update the role of the current user
			$user_role = Core::getUserRole('duckietown');
			if( in_array($user_role, ['user', 'engineer']) ){
				Core::setUserRole('participant', 'aido');
			}
			//
			self::$initialized = true;
			return ['success' => true, 'data' => null];
		}else{
			return ['success' => true, 'data' => "Module already initialized!"];
		}
	}//init

	/** Returns whether the module is initialized.
     *
     *	@retval boolean
	 *		whether the module is initialized.
     */
	public static function isInitialized(){
		return self::$initialized;
	}//isInitialized

    /** Safely terminates the module.
     *
     *	@retval array
	 *		a status array of the form
	 *	<pre><code class="php">[
	 *		"success" => boolean, 	// whether the function succeded
	 *		"data" => mixed 		// error message or NULL
	 *	]</code></pre>
	 *		where, the `success` field indicates whether the function succeded.
	 *		The `data` field contains errors when `success` is `FALSE`.
     */
	public static function close(){
		return [ 'success' => true, 'data' => null ];
	}//close



	// =======================================================================================================
	// Public functions

	public static function getChallenges(){
		$res = AIDO::callChallengesAPI( 'GET', 'challenges' );
		if( !$res['success'] ) return $res;
		// remove keys from $res['data']
		$res['data'] = array_values($res['data']);
		// return result
		return $res;
	}//getChallenges

	public static function getSubmissionsPerChallenge( $challenge_id, $dt_uid=null, $status=null, $page=null, $limit=null, $recent_first=true ){
		$res = AIDO::callChallengesAPI( 'GET', 'subs-by-challenge', $challenge_id );
		if( !$res['success'] ) return $res;
		// filter results (user_id)
		//TODO: this should be done by the API
		if( !is_null($dt_uid) ){
			$res['data'] = self::_filter_results_list($res['data'], 'user_id', $dt_uid);
		}
		// filter results (status)
		//TODO: this should be done by the API
		if( !is_null($status) ){
			$res['data'] = self::_filter_results_list($res['data'], 'status', $status);
		}
		// paginate results
		//TODO: this should be done by the API
		$res['total'] = count($res['data']);
		$res['data'] = self::_paginate_results_list($res['data'], $page, $limit);
		// return results
		return $res;
	}//getSubmissionsPerChallenge

	public static function getUserSubmissions( $user_id=null, $challenge=null, $status=null, $page=null, $limit=null, $recent_first=true, $keywords=null ){
		// get arguments
		$data = [
			'sort_by' => 'date',
			'sort_order' => $recent_first? 'DESC' : 'ASC'
		];
		if( !is_null($challenge) ) $data['challenge_id'] = $challenge.',';
		if( !is_null($status) ) $data['status'] = $status;
		if( !is_null($page) ) $data['page'] = $page;
		if( !is_null($limit) ) $data['results'] = $limit;
		if( !is_null($keywords) ) $data['keywords'] = $keywords;
		// call API
		return AIDO::callChallengesAPI( 'GET', 'submissions-list', null/*action*/, $data, []/*headers*/, $user_id );
	}//getUserSubmissions

	public static function getSubmission( $submission_id, $user_id=null ){
		// TODO: replace this inefficient call with the call to the `submission/{sid}` endpoint
		$res = self::getUserSubmissions( $user_id );
		if( !$res['success'] ) return $res;
		$subm = null;
		foreach( $res['data'] as $s ){
			if( strval($s['submission_id']) == strval($submission_id) ){
				$subm = $s;
				break;
			}
		}
		if( is_null($subm) )
			return ['success'=>false, 'data'=>sprintf('Submission with ID #%s not found', $submission_id)];
		// append `jobs` section
		$res = AIDO::callChallengesAPI( 'GET', 'jobs-by-submission', $submission_id, []/*data*/, []/*headers*/, $user_id );
		if( !$res['success'] ) return $res;
		// append `jobs`
		$subm['jobs'] = count($res['data'])>0? $res['data'] : [];
		return ['success'=>true, 'data'=>$subm];
	}//getSubmission

	public static function getUserSubmissionsStats( $user_id=null ){
		$res = AIDO::callChallengesAPI( 'GET', 'info', null/*action*/, []/*data*/, []/*headers*/, $user_id );
		if( !$res['success'] ) return $res;
		$res['data'] = $res['data']['stats'];
		return $res;
	}//getUserSubmissionsStats

	// public static function getSubmissions( $user_id=null, $status=null, $page=null, $limit=null, $recent_first=true ){
	// 	// create SQL filters
	// 	$query_parts = [
	// 		'SELECT' => '*',
	// 		'FROM' => 'aido_submission',
	// 		'WHERE' => [],
	// 		'GROUP' => null,
	// 		'ORDER' => [],
	// 		'LIMIT' => null
	// 	];
	// 	// filter by user ID
	// 	if( !is_null($user_id) )
	// 		array_push($query_parts['WHERE'], sprintf('`user` = \'%s\'', $user_id));
	// 	// filter by status
	// 	if( !is_null($status) )
	// 		array_push($query_parts['WHERE'], sprintf('`status` = \'%s\'', $status));
	// 	// sort results
	// 	array_push($query_parts['ORDER'], sprintf('datetime %s', $recent_first? 'ASC' : 'DESC'));
	// 	// filter by status
	// 	$offset = 0;
	// 	if( !is_null($page) )
	// 		$offset = max(0, ($page-1)) * ( is_null($limit)? 0 : $limit );
	// 	$size = PHP_INT_MAX;
	// 	if( !is_null($limit) )
	// 		$size = $limit;
	// 	$query_parts['LIMIT'] = sprintf('%d, %d', $offset, $size);
	// 	// create SQL query
	// 	$query = self::_build_select_mysql_query( $query_parts );
	// 	$res = self::$sql->query($query);
	// 	if( $res === false )
	// 		return ['success' => false, 'data' => self::$sql->error];
	// 	// get results
	// 	$submissions = [];
	//     // collect data of each row
	//     while( $row = $res->fetch_assoc() ){
	//         array_push($submissions, $row);
	//     }
	// 	// count total number of items
	// 	$query_parts['SELECT'] = 'COUNT(*) as total_items';
	// 	$query_parts['ORDER'] = [];
	// 	$query_parts['LIMIT'] = null;
	// 	// create SQL query
	// 	$query = self::_build_select_mysql_query( $query_parts );
	// 	$res = self::$sql->query($query);
	// 	if( $res === false )
	// 		return ['success' => false, 'data' => self::$sql->error];
	// 	$res = $res->fetch_assoc();
	// 	$total_size = $res['total_items'];
	// 	// return
	// 	return ['success' => true, 'data' => ['total' => $total_size, 'page_data' => $submissions]];
	// }//getSubmissions



	// public static function getUserSubmissionsStats( $user_id ){
	// 	// create stats
	// 	$stats = [
	// 		'Queued' => 0,
	// 		'Running' => 0,
	// 		'Finished' => 0,
	// 		'Failed' => 0,
	// 		'total' => 0
	// 	];
	// 	// create SQL filters
	// 	$query_parts = [
	// 		'SELECT' => '`status`, COUNT(*) AS count',
	// 		'FROM' => 'aido_submission',
	// 		'WHERE' => [ sprintf("`user` = '%s'", $user_id) ],
	// 		'GROUP' => 'status',
	// 		'ORDER' => [],
	// 		'LIMIT' => null
	// 	];
	// 	// create SQL query
	// 	$query = self::_build_select_mysql_query( $query_parts );
	// 	$res = self::$sql->query($query);
	// 	if( $res === false )
	// 		return ['success' => false, 'data' => self::$sql->error];
	// 	// update stats
	// 	// collect data of each row
	//     while( $row = $res->fetch_assoc() ){
	// 		$stats[$row['status']] = $row['count'];
	// 		$stats['total'] += $row['count'];
	//     }
	// 	// return
	// 	return ['success' => true, 'data' => $stats];
	// }//getUserSubmissionsStats

	// public static function getSubmission( $user_id, $submission_id ){
	// 	// create SQL filters
	// 	$query_parts = [
	// 		'SELECT' => '*',
	// 		'FROM' => 'aido_submission',
	// 		'WHERE' => [ sprintf("`user` = '%s'", $user_id), sprintf("`id` = '%s'", $submission_id) ],
	// 		'GROUP' => null,
	// 		'ORDER' => [],
	// 		'LIMIT' => null
	// 	];
	// 	// create SQL query
	// 	$query = self::_build_select_mysql_query( $query_parts );
	// 	$res = self::$sql->query($query);
	// 	if( $res === false )
	// 		return ['success' => false, 'data' => self::$sql->error];
	// 	// get result
	// 	if( $res->num_rows <= 0 )
	// 		return ['success' => false, 'data' => sprintf('No entry found with key `%s, %s`', $user_id, $submission_id)];
	// 	// return
	// 	return ['success' => true, 'data' => $res->fetch_assoc()];
	// }//getSubmission

	// public static function createSubmission( $user_id, $submission_label, $submission_content ){
	// 	if( is_array($submission_content) ) $submission_content = json_encode($submission_content);
	// 	// create SQL query parts
	// 	$query_parts = [
	// 		'INTO' => 'aido_submission',
	// 		'DATA' => [
	// 			'user' => sprintf("'%s'", self::$sql->real_escape_string($user_id)),
	// 			'id' => 'DEFAULT',
	// 			'label' => sprintf("'%s'", self::$sql->real_escape_string($submission_label)),
	// 			'datetime' => 'NOW()',
	// 			'status' => "'Queued'",
	// 			'content' => sprintf("'%s'", self::$sql->real_escape_string($submission_content))
	// 		]
	// 	];
	// 	// create SQL query
	// 	$query = self::_build_insert_mysql_query( $query_parts );
	// 	$res = self::$sql->query($query);
	// 	if( $res === false )
	// 		return ['success' => false, 'data' => self::$sql->error];
	// 	// success
	// 	return ['success' => true, 'data' => self::$sql->insert_id];
	// }//createSubmission
	//
	// public static function deleteSubmission( $user_id, $submission_id ){
	// 	$res = self::getSubmission($user_id, $submission_id);
	// 	if( !$res['success'] ) return $res;
	// 	$submission = $res['data'];
	// 	// check status
	// 	if( $submission['status'] == "Running" )
	// 		return ['success' => false, 'data' => 'The submission is in the Running status. It is not possible to delete a submission while it is being executed'];
	// 	// create SQL query parts
	// 	$query_parts = [
	// 		'FROM' => 'aido_submission',
	// 		'WHERE' => [ sprintf("`user` = '%s'", $user_id), sprintf("`id` = '%s'", $submission_id) ]
	// 	];
	// 	// create SQL query
	// 	$query = self::_build_delete_mysql_query( $query_parts );
	// 	$res = self::$sql->query($query);
	// 	if( $res === false )
	// 		return ['success' => false, 'data' => self::$sql->error];
	// 	// success
	// 	return ['success' => true, 'data' => null];
	// }//deleteSubmission


	// =======================================================================================================
	// Private functions

	// private static function _build_select_mysql_query( $query_parts ){
	// 	return sprintf(
	// 		'SELECT %s FROM %s WHERE %s %s %s %s',
	// 		$query_parts['SELECT'],
	// 		$query_parts['FROM'],
	// 		(count($query_parts['WHERE']) > 0)? implode(' AND ', $query_parts['WHERE']) : '1',
	// 		(!is_null($query_parts['GROUP']))? sprintf('GROUP BY `%s`', $query_parts['GROUP']) : '',
	// 		(count($query_parts['ORDER']) > 0)? 'ORDER BY '.implode(', ', $query_parts['ORDER']) : '',
	// 		(!is_null($query_parts['LIMIT']))? 'LIMIT '.$query_parts['LIMIT'] : ''
	// 	);
	// }//_build_select_mysql_query
	//
	// private static function _build_insert_mysql_query( $query_parts ){
	// 	$data_keys = array_keys($query_parts['DATA']);
	// 	return sprintf(
	// 		'INSERT INTO `%s`(%s) VALUES (%s)',
	// 		$query_parts['INTO'],
	// 		implode( ', ', array_map(function($k) { return sprintf('`%s`', $k); }, $data_keys) ),
	// 		implode( ', ', array_map(function($k) use ($query_parts) { return $query_parts['DATA'][$k]; }, $data_keys) )
	// 	);
	// }//_build_insert_mysql_query
	//
	// private static function _build_delete_mysql_query( $query_parts ){
	// 	return sprintf(
	// 		'DELETE FROM `%s`WHERE %s',
	// 		$query_parts['FROM'],
	// 		(count($query_parts['WHERE']) > 0)? implode(' AND ', $query_parts['WHERE']) : '0'
	// 	);
	// }//_build_delete_mysql_query

	private static function _filter_results_list( $data, $key, $values ){
		if( !is_array($values) ) $values = [$values];
		return array_filter($data, function($e)use($key,$values){return in_array($e[$key],$values);});
	}//_filter_results_list

	private static function _filter_results_list_by_text( $data, $key, $keywords ){
		return array_filter(
			$data,
			function($e)use($key,$keywords){
				return stripos($e[$key], $keywords) !== false;
			}
		);
	}//_filter_results_by_text_list

	private static function _paginate_results_list( $data, $page, $results_per_page ){
		return array_slice( $data, ($page-1)*$results_per_page, $results_per_page );
	}//_paginate_results_list

}//AIDODashboard
?>
