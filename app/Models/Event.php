<?php

namespace App\Models;

use App\Models\BaseModel;

class Event extends BaseModel
{	
	public function getEvent($type, $querydata=[], $requestdata=[], $extras=[])
    {  
    	$select 			= [];
		
		if(in_array('event', $querydata)){
			$data		= 	['e.*'];							
			$select[] 	= 	implode(',', $data);
		}
		
		$query = $this->db->table('event e');
		
		if(isset($extras['select'])) 					$query->select($extras['select']);
		else											$query->select(implode(',', $select));
		
		if(isset($requestdata['id'])) 					$query->where('e.id', $requestdata['id']);
		if(isset($requestdata['status'])) 				$query->whereIn('e.status', $requestdata['status']);
		if(isset($requestdata['userid'])) 				$query->where('e.user_id', $requestdata['userid']);
		if(isset($requestdata['upcoming'])) 			$query->where('e.start_date >=', $requestdata['upcoming']);
		if(isset($requestdata['past'])) 				$query->where('e.end_date <', $requestdata['past']);
		
		if($type!=='count' && isset($requestdata['start']) && isset($requestdata['length'])){
			$query->limit($requestdata['length'], $requestdata['start']);
		}
		if(isset($requestdata['order']['0']['column']) && isset($requestdata['order']['0']['dir'])){
			if(isset($requestdata['page']) && $requestdata['page']=='events'){
				$column = ['e.name', 'e.image', 'e.start_date', 'e.location', 'e.mobile', 'e.id'];
				$query->orderBy($column[$requestdata['order']['0']['column']], $requestdata['order']['0']['dir']);
			}
		}
		if(isset($requestdata['search']['value']) && $requestdata['search']['value']!=''){
			$searchvalue = $requestdata['search']['value'];
						
			if(isset($requestdata['page'])){
				$page = $requestdata['page'];
				
				$query->groupStart();
					if($page=='events'){				
						$query->like('e.name', $searchvalue);
						$query->orLike('e.location', $searchvalue);
						$query->orLike('e.mobile', $searchvalue);
					}
				$query->groupEnd();
			}			
		}
		
		if(isset($extras['groupby'])) 	$query->groupBy($extras['groupby']);
		else $query->groupBy('e.id');
		
		if(isset($extras['orderby'])) 	$query->orderBy($extras['orderby'], $extras['sort']);

		if($type=='count'){
			$result = $query->countAllResults();
		}else{
			$query = $query->get();
			
			if($type=='all') 		$result = $query->getResultArray();
			elseif($type=='row') 	$result = $query->getRowArray();
			
			if($type=='row' && in_array('barn', $querydata)){
				$eventdata = $result;
				$barndatas = $this->db->table('barn b')->where('b.status', '1')->where('b.event_id', $eventdata['id'])->get()->getResultArray();
				$result['barn'] = $barndatas;
				
				if(in_array('stall', $querydata)){
					if(count($barndatas) > 0){
						foreach($barndatas as $barnkey => $barndata){
							$stalldata = $this->db->table('stall s')->where('s.status', '1')->where('s.barn_id', $barndata['id'])->get()->getResultArray();
							$result['barn'][$barnkey]['stall'] = $stalldata;
						}
					}
				}
			}
		}
	
		return $result;
    }
	
	public function action($data)
	{ 	
		$this->db->transStart();
		
		$datetime			= date('Y-m-d H:i:s');
		$actionid 			= (isset($data['actionid'])) ? $data['actionid'] : '';
		$userid				= $data['userid'];
		
		$request['user_id'] = $userid;
		$request['status'] 	= '1';
		if(isset($data['name']) && $data['name']!='')      		        $request['name'] 			= $data['name'];
		if(isset($data['description']) && $data['description']!='')     $request['description']     = $data['description'];
		if(isset($data['location']) && $data['location']!='')           $request['location'] 		= $data['location'];
		if(isset($data['mobile']) && $data['mobile']!='')      	        $request['mobile'] 			= $data['mobile'];
		if(isset($data['start_date']) && $data['start_date']!='')       $request['start_date']		= date('Y-m-d', strtotime($data['start_date']));;
		if(isset($data['end_date']) && $data['end_date']!='')           $request['end_date'] 		= date('Y-m-d', strtotime($data['end_date']));		
		if(isset($data['start_time']) && $data['start_time']!='')       $request['start_time'] 		= $data['start_time'];
		if(isset($data['end_time']) && $data['end_time']!='')           $request['end_time'] 		= $data['end_time'];
		if(isset($data['stalls_price']) && $data['stalls_price']!='')   $request['stalls_price']	= $data['stalls_price'];
		if(isset($data['rvspots_price']) && $data['rvspots_price']!='') $request['rvspots_price'] 	= $data['rvspots_price'];
		if(isset($data['status']) && $data['status']!='')      		    $request['status'] 			= $data['status'];
		
		if(isset($data['image']) && $data['image']!=''){
 			$request['image'] = $data['image'];		
			filemove($data['image'], './assets/uploads/event');		
		}
		
		if(isset($data['eventflyer']) && $data['eventflyer']!=''){
 			$request['eventflyer'] = $data['eventflyer'];		
			filemove($data['eventflyer'], './assets/uploads/eventflyer');		
		}
		
		if(isset($data['stallmap']) && $data['stallmap']!=''){
 			$request['stallmap'] = $data['stallmap'];		
			filemove($data['stallmap'], './assets/uploads/stallmap');		
		}
		
		if(isset($request)){				
			$request['updated_at'] 	= $datetime;
			$request['updated_by'] 	= $userid;						
			
			if($actionid==''){
				$request['created_at'] 		= 	$datetime;
				$request['created_by'] 		= 	$userid;
				
				$event = $this->db->table('event')->insert($request);
				$eventinsertid = $this->db->insertID();
			}else{
				$event = $this->db->table('event')->update($request, ['id' => $actionid]);
				$eventinsertid = $actionid;
			}
		}
		 
		if(isset($data['barn']) && count($data['barn']) > 0){
			$barnidcolumn = array_filter(array_column($data['barn'], 'id'));
			if(count($barnidcolumn)){
				$this->db->table('barn')->whereNotIn('id', $barnidcolumn)->update(['status' => '0'], ['event_id' => $eventinsertid]);
			}
			
			foreach($data['barn'] as $barndata){
				$barnid       		= $barndata['id']!='' ? $barndata['id'] : '';
				$barn['event_id'] 	= $eventinsertid;
				$barn['name']     	= $barndata['name'];
				$barn['status']     = '1';
				
				if($barnid==''){
					$this->db->table('barn')->insert($barn);
					$barninsertid = $this->db->insertID();
				}else {
				   $this->db->table('barn')->update($barn, ['id' => $barnid]);
				   $barninsertid = $barnid;
				}	
				
				if(isset($barndata['stall']) && count($barndata['stall']) > 0){ 
        			$stallidcolumn = array_filter(array_column($barndata['stall'], 'id'));
        			if(count($stallidcolumn)){
        				$this->db->table('stall')->whereNotIn('id', $stallidcolumn)->update(['status' => '0'], ['barn_id' => $barninsertid]);
        			}
					
        			foreach($barndata['stall'] as $stalldata){
        				$stallid        	 = $stalldata['id']!='' ? $stalldata['id'] : '';
        				$stall['barn_id']    = $barninsertid;
        				$stall['name']       = $stalldata['name'];
        				$stall['price']      = $stalldata['price'];
        				$stall['status']     = $stalldata['status'];
        				
        				if($stallid==''){
        					$this->db->table('stall')->insert($stall);
        				}else {
        				   $this->db->table('stall')->update($stall, ['id' => $stallid]);
        				}	
        			}
        		}
			}
		}
		
		if(isset($eventinsertid) && $this->db->transStatus() === FALSE){
			$this->db->transRollback();
			return false;
		}else{
			$this->db->transCommit();
			return $eventinsertid;
		}
	}

	public function delete($data)
	{
		$this->db->transStart();
		
		$datetime		= date('Y-m-d H:i:s');
		$userid			= $data['userid'];
		$id 			= $data['id'];
		
		$event 			= $this->db->table('event')->update(['updated_at' => $datetime, 'updated_by' => $userid, 'status' => '0'], ['id' => $id]);
		
		if($event && $this->db->transStatus() === FALSE){
			$this->db->transRollback();
			return false;
		}else{
			$this->db->transCommit();
			return true;
		}
	}
}