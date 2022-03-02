<?php

namespace App\Models;

use App\Models\BaseModel;

class Users extends BaseModel
{	
	public function getUsers($type, $querydata=[], $requestdata=[], $extras=[])
    {  
        //print_r($requestdata);die;
    	$select 			= [];
		
		if(in_array('users', $querydata)){
			$data		= 	['u.*'];							
			$select[] 	= 	implode(',', $data);
		}
			
		$query = $this->db->table('users u');
				
		if(isset($extras['select'])) 					$query->select($extras['select']);
		else											$query->select(implode(',', $select));
		
		if(isset($requestdata['id'])) 					$query->where('u.id', $requestdata['id']);
		if(isset($requestdata['neqid'])) 				$query->where('u.id !=', $requestdata['neqid']);
		if(isset($requestdata['email']))         		$query->where('u.email', $requestdata['email']);	
		if(isset($requestdata['password'])) 			$query->where('u.password', md5($requestdata['password']));
		if(isset($requestdata['type'])) 				$query->whereIn('u.type', $requestdata['type']);
		if(isset($requestdata['status'])) 				$query->whereIn('u.status', $requestdata['status']);
		
		if($type!=='count' && isset($requestdata['start']) && isset($requestdata['length'])){
			$query->limit($requestdata['length'], $requestdata['start']);
		}
		if(isset($requestdata['order']['0']['column']) && isset($requestdata['order']['0']['dir'])){
			if(isset($requestdata['page']) && $requestdata['page']=='adminusers'){
				$column = ['u.name', 'u.email', 'u.type'];
				$query->orderBy($column[$requestdata['order']['0']['column']], $requestdata['order']['0']['dir']);
			}
		}
		if(isset($requestdata['search']['value']) && $requestdata['search']['value']!=''){
			$searchvalue = $requestdata['search']['value'];
						
			if(isset($requestdata['page'])){
				$page = $requestdata['page'];
				
				$query->groupStart();
					if($page=='adminusers'){				
						$query->like('u.name', $searchvalue);
						$query->orLike('u.email', $searchvalue);
						$query->orLike('u.type', $searchvalue);
					}
				$query->groupEnd();
			}			
		}
		
		if(isset($extras['groupby'])) 	$query->groupBy($extras['groupby']);
		else $query->groupBy('u.id');
		
		if($type=='count'){
			$result = $query->countAllResults();
		}else{
			$query = $query->get();
			//echo $this->db->getLastQuery();die;
			
			if($type=='all') 		$result = $query->getResultArray();
			elseif($type=='row') 	$result = $query->getRowArray();
		}
		//print_r($result);die;
	
		return $result;
    }
	
	public function action($data)
	{
		$this->db->transStart();
		
		$datetime			= date('Y-m-d H:i:s');
		$userid				= getUserID();
		
		$actionid 			= (isset($data['actionid'])) ? $data['actionid'] : '';
		
		if(isset($data['name']) && $data['name']!='')      		$request['name'] 				= $data['name'];
		if(isset($data['email']) && $data['email']!='') 	 	$request['email'] 				= $data['email'];
		if(isset($data['password']) && $data['password']!='')	$request['password'] 			= md5($data['password']);
		if(isset($data['type']) && $data['type']!='') 	  		$request['type'] 				= $data['type'];
		if(isset($data['status']) && $data['status']!='') 	  	$request['status'] 				= $data['status'];
		if(isset($data['email_status']) && $data['email_status']!='') 	  	$request['email_status'] = $data['email_status'];

		
		if(isset($request)){				
			$request['updated_at'] 	= $datetime;
			$request['updated_by'] 	= $userid;						
			
			if($actionid==''){
				$request['created_at'] 		= 	$datetime;
				$request['created_by'] 		= 	$userid;
				
				$users = $this->db->table('users')->insert($request);
				$usersinsertid = $this->db->insertID();
			}else{
				$users = $this->db->table('users')->update($request, ['id' => $actionid]);
				$usersinsertid = $actionid;
			}
		}
		
		if(isset($usersinsertid) && $this->db->transStatus() === FALSE){
			$this->db->transRollback();
			return false;
		}else{
			$this->db->transCommit();
			return $usersinsertid;
		}
	}

	public function delete($data)
	{
		$this->db->transStart();
		
		$datetime		= date('Y-m-d H:i:s');
		$userid			= getUserID();
		$id 			= $data['id'];
		
		$users 			= $this->db->table('users')->update(['updated_at' => $datetime, 'updated_by' => $userid, 'status' => '0'], ['id' => $id]);
		
		if($users && $this->db->transStatus() === FALSE){
			$this->db->transRollback();
			return false;
		}else{
			$this->db->transCommit();
			return true;
		}
	}
}