<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, July 18th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele



namespace system\packages\aido_participant;

// require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Core.php';
// require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Configuration.php';
// require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Utils.php';

use \system\classes\Core;
use \system\classes\Utils;
use \system\classes\Database;
use \system\classes\Configuration;

/**
*   Module for managing AIDO submissions, monitoring the Robotarium, etc.
*/
class AIDOParticipant{

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
			//
			self::$initialized = true;
			return array( 'success' => true, 'data' => null );
		}else{
			return array( 'success' => true, 'data' => "Module already initialized!" );
		}
	}//init

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
        // do stuff
		return array( 'success' => true, 'data' => null );
	}//close



	// =======================================================================================================
	// System info functions


	//TODO: This should be moved to next class AIDOSupervisor
	public static function getSubmissions(){
		$submissions = [];
		// get all users
		foreach( Core::getUserList() as $user_id ){
			if( !Database::database_exists('aido_participant', 'submission_u'.$user_id) ) continue;
			// open user's submissions DB
			$subm_db = new Database( 'aido_participant', 'submission_u'.$user_id );
			// read all the entries
			foreach( $subm_db->list_keys() as $key ){
				$res = $subm_db->read( $key );
				if( !$res['success'] ) return $res;
				// get entry
				$subm_entry = $res['data'];
				array_push($submissions, $subm_entry);
			}
		}
		// return all submissions
		return $submissions;
	}//getSubmissions

	public static function getUserSubmissions( $user_id ){
		$submissions = [];
		// open submissions DB
		$subm_db = new Database( 'aido_participant', 'submission_u'.$user_id );
		// read all the entries
		foreach( $subm_db->list_keys() as $key ){
			$res = $subm_db->read( $key );
			if( !$res['success'] ) return $res;
			// get entry
			$subm_entry = $res['data'];
			array_push($submissions, $subm_entry);
		}
		return $submissions;
	}//getUserSubmissions

	public static function getSubmission( $user_id, $submission_key ){
		// open submissions DB
		$subm_db = new Database( 'aido_participant', 'submission_u'.$user_id );
		// get entry (not existence errors managed by Database)
		return $subm_db->read( $submission_key );
	}//getSubmission


	//TODO: in the API call that wxecutes this function make sure we open/close the session so that we guarantee single access to the DB
	public static function createSubmission( $user_id, $submission_label, $submission_content ){
		// open submissions DB
		$subm_db = new Database( 'aido_participant', 'submission_u'.$user_id );
		// create entry
		$timezone = 'GMT';
		$now = new \DateTime( 'now', new \DateTimeZone($timezone) );
		$entry = [
			'id' => -1,
			'label' => $submission_label,
			'datetime' => $now->format( \DateTime::W3C ),
			'datetime_format' => 'W3C',
			'datetime_timezone' => $timezone,
			'status' => 'Queued',
			'content' => $submission_content
		];
		// get next key
		$submission_key = 1;
		if( $subm_db->size() > 0 )
			$submission_key = max( $subm_db->list_keys() ) + 1;
		// update key
		$entry['id'] = $submission_key;
		// add entry to DB
		return $subm_db->write( $submission_key, $entry );
	}//createSubmission

	// TODO: add your methods here

}//AIDOParticipant
?>
