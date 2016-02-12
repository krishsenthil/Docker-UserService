<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 *
 * Create ICS Event
 *
 *  Ref: https://www.ietf.org/rfc/rfc2445.txt
 * 
 * @todo : Tested with only mac clander
 * @author Vijay <vbose@Collash.com>
 * @category   Noobh
 * @package    Noobh_ICS
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 * @since   0.1
 * @date Oct 10, 2014
 */
class Noobh_Ics_Event extends Noobh_Ics_Abstract
{
	/** 
	 * Constants for ics file creation
	 */
	const DELIMITER = "\n";
	/**
	 * Store event start date time
	 * @var {sting}
	 */
	private $_startDateTime;
	/**
	 * Store event end date time
	 * @var {sting}
	 */
	private $_endDateTime;
	/**
	 * Store event time zone
	 * @var {sting}
	 */
	private $_eventTimezone;
	/**
	 * Store event summary
	 * @var {sting}
	 */
	private $_summary;
	/**
	 * Store event description
	 * @var {sting}
	 */
	private $_description;
	/**
	 * Store event location
	 * @var {sting}
	 */
	private $_location;

	/** 
	 * Store add URL for an event
	 * @var {string}
	 */
	private $_url;
	/** 
	 * Create ICS content
	 * @public public
	 * @param  void
	 * @return  void
	 */
	public function __construct(){}

	/** 
	 *  Set start date time for the event
	 *  @param {string} $startDateTime 
	 *  @param {void}
	 */
	public function setStartDateTime($startDateTime){
		$this->_startDateTime = $startDateTime;
	}
	/** 
	 *  Set end date time for the event
	 *  @param {string} $endDateTime 
	 *  @param {void}
	 */
	public function setEndDateTime($endDateTime){
		$this->_endDateTime = $endDateTime;
	}
	/** 
	 *  Set event timezone
	 *  @param {string} $timezone 
	 *  @param {void}
	 */
	public function setTimezone($timezone){
		$this->_eventTimezone = $timezone;
	}	
	/** 
	 *  Set event summary
	 *  @param {string} $summary 
	 *  @param {void}
	 */
	public function setSummary($summary){
		$this->_summary = $summary;
	}
	/** 
	 *  Set event location
	 *  @param {string} $location 
	 *  @param {void}
	 */
	public function setLocation($location){
		$this->_location = $location;
	}
	/** 
	 *  Set event description
	 *  @param {string} $description 
	 *  @param {void}
	 */
	public function setDescription($description){
		$this->_description = $description;
	}
	/** 
	 *  Set url {Equalent to Add Url}
	 *  @param {string} $url 
	 *  @param {void}
	 */
	public function setUrl($url){
		$this->_url = $url;
	}
	/** 
	 *  Get start date time for the event
	 *  @param {void}
	 *  @param {string} $startDateTime 
	 */
	public function getStartDateTime(){
		return $this->_startDateTime;
	}
	/** 
	 *  Get end date time for the event
	 *  @param {void}
	 *  @param {string} $endDateTime 
	 */
	public function getEndDateTime(){
		return $this->_endDateTime;
	}
	/** 
	 *  Get event timezone
	 *  @param {void}
	 *  @param {string} $timezone 
	 */
	public function getTimezone(){
		return $this->_eventTimezone;
	}	
	/** 
	 *  Get event summary
	 *  @param {void}
	 *  @param {string} $summary
	 */
	public function getSummary(){
		return $this->_summary;
	}
	/** 
	 *  Get event location
	 *  @param {void}
	 *  @param {string} $location
	 */
	public function getLocation(){
		return $this->_location;
	}
	/** 
	 *  Get event description
	 *  @param {void}
	 *  @param {string} $location
	 */
	public function getDescription(){
		return $this->_description;
	}
	/** 
	 *  Get added url
	 *  @param {void}
	 *  @param {string} $url
	 */
	public function getUrl(){
		return $this->_url;
	}
	/**
	 * Create multiple
	 * @param  {mixed} array | Noobh_Ics_Event
	 *
	 *  Array : Should be an object of Noobh_Ics_Events
	 * 
	 * @return {string} $icsContent
	 */
	public static function create($event){
		$escapeCharacters = array(",",";","\n","\N");
		$escapedCharacters = array("\,","\;","\\n","\\N");
        if(!is_array($event)){
        	if($event instanceof Noobh_Ics_Event == false){
			 //Throw exception
			 throw new Exception('Parameter should be instance of Noobh_Ics_Event or array of Noobh_Ics_Event instance');
			}else{
				$event = array($event);
			}
		}
		//Store ics event file content
        $icsContent = "BEGIN:VCALENDAR\n";
        $icsContent .= "CALSCALE:GREGORIAN\n";
        $icsContent .= "VERSION:2.0\n";
		foreach($event as $singleEvent){
			if($singleEvent instanceof Noobh_Ics_Event){
				$created = date("Y-m-d\TH:i:s.000\Z", strtotime(date("now")));
				$sess_start_date = date('Ymd', strtotime($singleEvent->getStartDateTime()));
                $sess_start_time = date('His', strtotime($singleEvent->getStartDateTime()));
                $sess_end_date = date('Ymd', strtotime($singleEvent->getEndDateTime()));
                $sess_end_time = date('His', strtotime($singleEvent->getEndDateTime()));
                $location = str_replace($escapeCharacters,$escapedCharacters,$singleEvent->getLocation());
				$summary = str_replace($escapeCharacters,$escapedCharacters,$singleEvent->getSummary());

				$icsContent .= "BEGIN:VEVENT\n";
				$icsContent .= "CREATED:".$created."\n";
				$icsContent .= "TRANSP:OPAQUE\n";
				$icsContent .= "SUMMARY:" . $summary . "\n";
                $icsContent .= "DTSTART;TZID=" . $singleEvent->getTimezone() . ":" . $sess_start_date . "T" . $sess_start_time . "\n";
                $icsContent .= "DTEND;TZID=" . $singleEvent->getTimezone() . ":" . $sess_end_date . "T" . $sess_end_time . "\n";
                $icsContent .= "DTSTAMP;TZID=" . $singleEvent->getTimezone() . ":" . date('Ymd') . "T" . date('His') . "\n";
                $icsContent .= "LOCATION:" . $location . "\n";
                if($singleEvent->getDescription()){
                	$icsContent .= "DESCRIPTION:". $singleEvent->getDescription() ."\n";
                }
                $icsContent .= 'UID:' . sha1(uniqid()) . "\n";
                $icsContent .= "SEQUENCE:3\n";
                //Add url if exist
                if($singleEvent->getUrl()){
                	$icsContent .= "URL;VALUE=URI:". urlencode($singleEvent->getUrl())."\n";
                }
                $icsContent .= "END:VEVENT\n";
			}else{
				//Throw exception
				throw new Exception('Param list contain a non-instance of Noobh_Ics_Event');
			}
		}
		$icsContent .= "END:VCALENDAR\n"; 
		return $icsContent;
	}

}