<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller {


	public function index()
	{
		$this->load->model('clusterpoint');
		$data=[];
		$data['view']='search';
		$data['js']=[
			'jquery-ui.min',
			'datepicker-lv',
			'jquery.qubit',
			'jquery.bonsai',
			'jquery.cookie',
			'search'
		];
		$global_statistics=$data['global_statistics']=$this->clusterpoint->statistics();
		$data['speakers']=$this->clusterpoint->speakers();
		$data['periods']=$this->clusterpoint->getPeriods();
		$request=$data['request']=false;
		$req_id=$this->uri->segment(3);
		if($req_id && preg_match('/^[0-9A-Z\-]+$/i',$req_id) && file_exists('tmp/'.$req_id.'.txt'))
		{
			$request=$data['request']=json_decode(file_get_contents('tmp/'.$req_id.'.txt'),true);
		}
		$data['page_string']='';
		if($data['request'])
		{
			$limit=100;
			$page=intval($this->uri->segment(4));
			$result=$data['result']=$this->clusterpoint->search($data['request'],$limit,$page*$limit);
			// print_r($data['result']);
			// die();
			
			if(isset($data['request']['params']))
			{
				$data['params']=$this->_format_caegories($global_statistics['category'],$data['request']['params']);
			}
			else
			{
				$data['params']=$this->_format_caegories($result['statistics']['category'],[]);
			}
			
			$this->load->helper('pagination_helper');
			$url_base=site_url('search/results/'.$req_id.'/%lpp%/');
			$data['page_string']=page_string($result['hits'],$limit,$page,$url_base);
			
		}
		else
		{
			$data['params']=$this->_format_caegories($global_statistics['category'],[]);
		}
		if((isset($request['date']) && ($request['date']['to'] || $request['date']['from'])) || !(isset($result) && isset($result['date']))) 
		{
			$data['date']=$global_statistics['date'];
		}
		else
		{
			$data['date']=$result['statistics']['date'];
		}	
		$this->load->view('main',$data);
		return;
	}
	
	public function results()
	{
		$this->index();
	}
	
	private function _format_caegories(&$category,$selected)
	{
		$slt=[]; //sorted linear tree
		$c='';
		$cid=0;
		$sc='';
		$scid=0;
		$speakers=$this->clusterpoint->speakers();
		foreach($category AS $item)
		{
			if($c!=$item->category)
			{
				$slt[]=[
					'name'=>$item->category,
					'long_name'=>$item->category,
					'level'=>0,
					'parent'=>-1,
					'checked'=>in_array($item->category,$selected)
				];
				$c=$item->category;
				$cid=count($slt)-1;
				$sc='';
			}
			
			if($sc!=$item->subcategory)
			{
				$slt[]=[
					'name'=>$item->subcategory,
					'long_name'=>$item->subcategory,
					'level'=>1,
					'parent'=>$cid,
					'checked'=>in_array($item->category.'|'.$item->subcategory,$selected)
				];
				$sc=$item->subcategory;
				$scid=count($slt)-1;
			}
			
			
			$speaker=$speakers[$item->speaker];
			$slt[]=[
				'name'=>$item->speaker,
				'long_name'=>$speaker->name.
					(isset($speaker->year) ? ' ('.$speaker->year.')' : '').
					($item->category=='Ārvalstu viesi' || $item->subcategory=='Citi' ? ', '.str_replace('_',' ',$item->role) : ''),
				'level'=>2,
				'parent'=>$scid,
				'checked'=>in_array($item->category.'|'.$item->subcategory.'|'.$item->speaker,$selected)
			];
		}
		
		return $slt;
	}
	
	function context()
	{
		$this->load->model('clusterpoint');
		$req=$this->input->get();
		if(!isset($req['sequence']) || !isset($req['source']) || !isset($req['query'])) //invalid request
			return;
		$data['context']=$this->clusterpoint->getContext($req);
		$data['speakers']=$this->clusterpoint->speakers();
		// print_r($data['content']);die();
		$this->load->view('context',$data);
	}
	
	function get_result_url()
	{
		$req=$this->input->post();
		$guid=str_replace(['{','}'],'',uniqid());
		file_put_contents('tmp/'.$guid.'.txt',json_encode($req));
		echo site_url('search/results/'.$guid);
	}
}
