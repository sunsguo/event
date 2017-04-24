<?php
class Event{
	/**
	 * 活动id
	 * @var int
	 */
	public $id;
	
	/**
	 * 活动标题
	 * @var string 
	 */
	public $title;
	
	/**
	 * 活动描述
	 * @var string
	 */
	public $description;
	
	/**
	 * 活动起始时间
	 * @var string
	 */
	public $start;
	
	/**
	 * 活动结束时间
	 * @var string
	 */
	public $end;
	
	
	public function __construct($event){
		if(is_array($event)){
			$this->id = $event['event_id'];
			$this->title = $event['event_title'];
			$this->description = $event['event_desc'];
			$this->start = $event['event_start'];
			$this->end = $event['event_end'];
		}else {
			throw Exceptioin('no event data was supplied.');
		}
	}
	
	
	
	
	
	
	
}