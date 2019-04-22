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

  private static $initialized = false;
  private static $external_dashboard_fmt = "https://challenges.duckietown.org/v4/humans/%s/%s";

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
		if(!self::$initialized){
			// register new user types
			Core::registerNewUserRole('aido', 'candidate');
			Core::registerNewUserRole('aido', 'participant', 'dashboard');
			// set the user role to be an `aido:candidate` (by default)
			Core::setUserRole('candidate', 'aido');
			// update the role of the current user
			$user_role = Core::getUserRole('duckietown');
			if(in_array($user_role, ['user', 'engineer'])){
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
		$res = AIDO::callChallengesAPI('GET', 'challenges');
		if(!$res['success'])
      return $res;

    // add challenge_id to challenge object
    foreach($res['data'] as $challenge_id => &$ch)
      $ch['challenge_id'] = $challenge_id;

		// remove keys from data
		$res['data'] = array_filter(
      array_values($res['data']),
      function($ch){
        $aido_key = sprintf("aido%s", AIDO::getAIDOversion());
        return in_array($aido_key, $ch['tags']);
      }
    );
    $res['data'] = array_values($res['data']);

		// return result
		return $res;
	}//getChallenges

	public static function getSubmissionsPerChallenge($challenge_id, $dt_uid=null, $status=null, $page=null, $limit=null, $recent_first=true){
		$res = AIDO::callChallengesAPI('GET', 'subs-by-challenge', $challenge_id);
		if(!$res['success']) return $res;
		// filter results (user_id)
		//TODO: this should be done by the API
		if(!is_null($dt_uid)){
			$res['data'] = self::_filter_results_list($res['data'], 'user_id', $dt_uid);
		}
		// filter results (status)
		//TODO: this should be done by the API
		if(!is_null($status)){
			$res['data'] = self::_filter_results_list($res['data'], 'status', $status);
		}
		// paginate results
		//TODO: this should be done by the API
		$res['total'] = count($res['data']);
		$res['data'] = self::_paginate_results_list($res['data'], $page, $limit);
		// return results
		return $res;
	}//getSubmissionsPerChallenge

	public static function getUserSubmissions($user_id=null, $challenges=null, $status=null, $page=null, $limit=null, $recent_first=true, $keywords=null){
		// get arguments
		$data = [
			'sort_by' => 'date',
			'sort_order' => $recent_first? 'DESC' : 'ASC'
		];
		if(!is_null($challenges) && count($challenges) > 0)
      $data['challenge_id'] = is_array($challenges)? implode(',', $challenges) : $challenges;
		if(!is_null($status)) $data['status'] = $status;
		if(!is_null($page)) $data['page'] = $page;
		if(!is_null($limit)) $data['results'] = $limit;
		if(!is_null($keywords)) $data['keywords'] = $keywords;
		// call API
		return AIDO::callChallengesAPI('GET', 'submissions-list', null/*action*/, $data, []/*headers*/, $user_id);
	}//getUserSubmissions

  public static function getUserInfo($user_id=null){
		// call API
		return AIDO::callChallengesAPI('GET', 'user-info', null/*action*/, []/*data*/, []/*headers*/, $user_id);
	}//getUserInfo

	public static function getSubmission($submission_id, $user_id=null){
		// TODO: replace this inefficient call with the call to the `submission/{sid}` endpoint
		$res = self::getUserSubmissions($user_id);
		if(!$res['success']) return $res;
		$subm = null;
		foreach($res['data'] as $s){
			if(strval($s['submission_id']) == strval($submission_id)){
				$subm = $s;
				break;
			}
		}
		if(is_null($subm))
			return ['success'=>false, 'data'=>sprintf('Submission with ID #%s not found', $submission_id)];
		// append `jobs` section
		$res = AIDO::callChallengesAPI('GET', 'jobs-by-submission', $submission_id, []/*data*/, []/*headers*/, $user_id);
		if(!$res['success']) return $res;
		// append `jobs`
		$subm['jobs'] = count($res['data'])>0? $res['data'] : [];
		return ['success'=>true, 'data'=>$subm];
	}//getSubmission

	public static function getUserSubmissionsStats($user_id=null){
		$res = AIDO::callChallengesAPI('GET', 'info', null/*action*/, []/*data*/, []/*headers*/, $user_id);
		if(!$res['success']) return $res;
		$res['data'] = $res['data']['stats'];
		return $res;
	}//getUserSubmissionsStats

	public static function retireSubmission($submission_id, $user_id=null){
		$res = AIDO::callChallengesAPI('DELETE', 'submissions', null, ['submission_id' => $submission_id]/*data*/, []/*headers*/, $user_id);
		if(!$res['success']) return $res;
		return $res;
	}//retireSubmission

  public static function linkToExternalResource($type, $key){
    return sprintf(self::$external_dashboard_fmt, $type, $key);
  }//linkToExternalResource


	// =======================================================================================================
	// Private functions

	private static function _filter_results_list($data, $key, $values){
		if(!is_array($values)) $values = [$values];
		return array_filter($data, function($e)use($key,$values){return in_array($e[$key],$values);});
	}//_filter_results_list

	private static function _filter_results_list_by_text($data, $key, $keywords){
		return array_filter(
			$data,
			function($e)use($key,$keywords){
				return stripos($e[$key], $keywords) !== false;
			}
		);
	}//_filter_results_by_text_list

	private static function _paginate_results_list($data, $page, $results_per_page){
		return array_slice($data, ($page-1)*$results_per_page, $results_per_page);
	}//_paginate_results_list

}//AIDODashboard
?>
