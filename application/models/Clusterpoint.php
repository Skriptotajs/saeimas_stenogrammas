<?php
require_once('application/libraries/clusterpoint/cps_api.php');
class Clusterpoint
{
	public function getPeriods()
	{
		return 	['Saeimas'=>['5.&nbsp; Saeima'=>['1993/07/06','1995/11/06']
				,'6. &nbsp;Saeima'=>['1995/11/07','1998/11/02']
				,'7. &nbsp;Saeima'=>['1998/11/03','2002/11/04']
				,'8. &nbsp;Saeima'=>['2002/11/05','2006/11/06']
				,'9. &nbsp;Saeima'=>['2006/11/07','2010/11/01']
				,'10. Saeima'=>['2010/11/02','2011/11/16']
				,'11. Saeima'=>['2011/11/17','2014/11/03']
				],'Valdības'=>['1993/03/08 Valdis Birkavs'=>['1993/03/08','1994/09/18']
				,'1994/09/19 Māris Gailis'=>['1994/09/19','1995/12/20']
				,'1995/12/21 Andris Šķēle'=>['1995/12/21','1997/02/12']
				,'1997/02/13 Andris Šķēle'=>['1997/02/13','1997/08/06']
				,'1997/08/07 Guntars Krasts'=>['1997/08/07','1998/11/25']
				,'1998/11/26 Vilis Krištopans'=>['1998/11/26','1999/07/15']
				,'1999/07/16 Andris Šķēle'=>['1999/07/16','2000/05/04']
				,'2000/05/05 Andris Bērziņš'=>['2000/05/05','2002/11/06']
				,'2002/11/07 Einars Repše'=>['2002/11/07','2004/03/08']
				,'2004/03/09 Indulis Emsis'=>['2004/03/09','2004/12/01']
				,'2004/12/02 Aigars Kalvītis'=>['2004/12/02','2006/11/06']
				,'2006/11/07 Aigars Kalvītis'=>['2006/11/07','2007/12/19']
				,'2007/12/20 Ivars Godmanis'=>['2007/12/20','2009/03/11']
				,'2009/03/12 Valdis Dombrovskis'=>['2009/03/12','2010/11/01']
				,'2010/11/02 Valdis Dombrovskis'=>['2010/11/02','2011/10/23']
				,'2011/10/24 Valdis Dombrovskis'=>['2011/10/24','2014/01/21']
				,'2014/01/22 Laimdota Straujuma'=>['2014/01/22','2014/11/04']]];
	}
	
	var $conn;
	var $connectionStrings = array(
			'tcp://78.154.146.20:9007',
			'tcp://78.154.146.21:9007',
			'tcp://78.154.146.22:9007',
			'tcp://78.154.146.23:9007', 
		);
	
	public function __construct()
	{
		$this->conn=new CPS_Connection(new CPS_LoadBalancer($this->connectionStrings), 'datubaze', 'lietotajvards', 'parole', 'document', '//document/id', array('account' => 434));
		
		$this->speaker_data=$this->load_speakers();
	}
	
	public function load_speakers()
	{
		$cpsSearch = new CPS_SearchRequest(['type'=>'speaker'],0,10000);
		$cpsSearch->setOrdering([
			CPS_StringOrdering('name','lv')
		]);
		$searchResponse = $this->conn->sendRequest($cpsSearch);
		$docs=$searchResponse->getDocuments(DOC_TYPE_STDCLASS);
		
		return $docs;
	}
	
	public function speakers()
	{
		return $this->speaker_data;
	}
	var $statistic_queries=[
			"date"=>"MIN(date) AS min_date,MAX(date) AS max_date",
			"category"=>"category,subcategory,role,speaker GROUP BY category,subcategory,role,speaker ORDER BY category,subcategory,speaker",
			"session_type"=>"session_type GROUP BY session_type ORDER BY session_type"
		];
	var $order=[
		'Ārvalstu viesi'=>[
			'Citi'=>5,
			'Eiropas Savienība'=>3,
			'NATO'=>4,
			'Parlamenti'=>2,
			'Prezidenti'=>1,
			'Deputāti'=>1
		],
		'Latvijas institūciju pārstāvji'=>[
			'Citi'=>4,
			'Latvijas Banka'=>3,
			'Tiesībsargi'=>2,
			'Valsts kontrolieri'=>1
		],
		'Ministriju amatpersonas'=>[
			'Ministru prezidenta biedri'=>1,
			'Ministru prezidenta kandidāti'=>2,
			'Ministru kabineta pārstāvji'=>3,
			'Parlamentārie sekretāri'=>4
		],
		'Valdība'=>[
			'Ministri'=>3,
			'Ministru prezidenti'=>2,
			'Prezidenti'=>1
		],
		'Saeimas amatpersonas'=>[
			'Citi'=>5,
			'Saeimas priekšsēdētāji'=>1,
			'Priekšsēdētāja biedri'=>2,
			'Saeimas sekretāri'=>3,
			'Sekretāra biedri'=>4,
			'Sēdes vadītāji'=>7,
			'Skolēnu Saeima'=>6
		]
	];
	
	public function setStatistics(&$searchRequest)
	{
		$searchRequest->setAggregate($this->statistic_queries);
	}
	
	public function getStatistics(&$searchResponse)
	{
		$docs=$searchResponse->getAggregate(DOC_TYPE_STDCLASS);
		
		$statistics=[];
		
		$date=$docs[$this->statistic_queries['date']];
		
		if(isset($date->min_date))
			$statistics['date']=[
				'from'=>$date->min_date,
				'to'=>$date->max_date,
			];
		
		$statistics['session_type']=[];
		if(!is_array($docs[$this->statistic_queries['session_type']]) && isset($docs[$this->statistic_queries['session_type']]->session_type))
			$docs[$this->statistic_queries['session_type']]=[$docs[$this->statistic_queries['session_type']]];
		foreach($docs[$this->statistic_queries['session_type']] AS $item)
		{
			if($item->session_type)
				$statistics['session_type'][]=$item->session_type;
		}
		
		if(!is_array($docs[$this->statistic_queries['category']]) && isset($docs[$this->statistic_queries['category']]->category))
			$category=[$docs[$this->statistic_queries['category']]];
		else if(!is_array($docs[$this->statistic_queries['category']]))
			$category=[];
		else
			$category=$docs[$this->statistic_queries['category']];
			
		$order=[[],[],[]];
		foreach($category AS $item)
		{
			$order[0][]=$item->category;
			
			if(isset($this->order[$item->category]) && isset($this->order[$item->category][$item->subcategory]))
				$order[1][]=$this->order[$item->category][$item->subcategory];
			else
				$order[1][]=$item->subcategory;
			
			$order[2][]=$item->speaker;
		}
		
		// print_r($order);
		// print_r($category);
		array_multisort($order[0],$order[1],$order[2],$category);
		
		
		
		$statistics['category']=$category;
		// print_r($slt);die();
		return $statistics;
	}
	
	public function statistics()
	{
		$searchRequest = new CPS_SearchRequest(['type'=>'speech'],0,0);
		$this->setStatistics($searchRequest);
		$searchResponse = $this->conn->sendRequest($searchRequest);
		return $this->getStatistics($searchResponse);
	}
	
	public function parese_query($s)
	{
		$q='';
		$s.=' ';
		$state='in_space';
		for($i=0;$i<mb_strlen($s);$i++)
		{
			$c=mb_substr($s,$i,1);
			switch($state)
			{
				case 'in_space':
					if(!ctype_space($c))
					{
						$start=$i;
						$state='in_token';
						$alpha=true;
						$i--;
					}
				break;
				case 'in_token':
					if(ctype_space($c))
					{
						$state='in_space';
						$token=' '.mb_substr($s,$start,$i-$start).' ';
						if($alpha)
						{
							$token=' $$ lv'.$token.'$ ';
						}
						$q.=$token;
						
					}
					else if($c=='"')
					{
						$state='in_quotes';
						$q.=' "';
					}
					else if(!preg_match('/[[:alpha:]]/ui',$c))
					{
						$alpha=false;
					}
				break;
				case 'in_quotes':
					if($c=='"')
					{
						$q.='" ';
						$state='in_space';
					}
					else
					{
						$q.=$c;
					}
				break;
			}
		}
		
		return $q;
	}
	
	public function search($request)
	{
		$q=[];
		$q['type']='speech';
		
		if($request['query'])
		{
			$q['text']=$this->parese_query($request['query']);
		}
		
		if(isset($request['session_type']))
		{
			$q['session_type']='{="'.implode('" ="',$request['session_type']).'"}';
		}
		
		if($request['date']['from'] && $request['date']['to'])
		{
			$q['date']=$request['date']['from'] . ' .. ' . $request['date']['to'];
		}
		else if($request['date']['from'])
		{
			$q['date']=' >= '.$request['date']['from'];
		}
		else if($request['date']['to'])
		{
			$q['date']=' <= '.$request['date']['from'];
		}
		
		$params='';
		if(isset($request['params']))
		{
			$tags=['category','subcategory','speaker'];
			$params.='{';
			foreach($request['params'] AS $param)
			{
				$parts=explode('|',$param);
				$params.='(';
				
				foreach($parts AS $key=>$value)
				{
					$params.=CPS_Term('="'.$value.'"',$tags[$key]);
				}
				
				$params.=')';
			}
			$params.='}';
		}
		
		$query=CPS_QueryArray($q).$params;
		
		//echo htmlspecialchars($query); die();
		
		$cpsSearch = new CPS_SearchRequest($query,0,100);
		$cpsSearch->setList([
			'text'=>'highlight'
		]);
		
		$this->setStatistics($cpsSearch);
		$cpsSearch->setOrdering([CPS_RelevanceOrdering(), CPS_DateOrdering('date','desc')]);
		if(isset($request['order']))
		{
			switch($request['order'])
			{
				case 'date_asc':
					$cpsSearch->setOrdering([CPS_DateOrdering('date','asc'),CPS_RelevanceOrdering()]);
				break;
				case 'date_desc':
					$cpsSearch->setOrdering([CPS_DateOrdering('date','desc'),CPS_RelevanceOrdering()]);
					var_dump([CPS_DateOrdering('date','desc'),CPS_RelevanceOrdering()]);
				break;
				case 'relevence':
					$cpsSearch->setOrdering([CPS_RelevanceOrdering(), CPS_DateOrdering('date','desc')]);
				break;
			}
		}
		
		$searchResponse = $this->conn->sendRequest($cpsSearch);
		$docs=$searchResponse->getDocuments(DOC_TYPE_STDCLASS);
		
		foreach($docs AS &$doc)
		{
			$text=$doc->text;
			$n=mb_strlen($text);
			$offset=0;
			$marked_min_context=50;
			$segments=[];
			
			while(($offset=mb_strpos($text,'<b>',$offset))!==false)
			{
				$begin=intval(mb_strrpos($text,'.',$offset-$n-($offset>$marked_min_context ? $marked_min_context : 0)));
				$marked_end=mb_strpos($text,'</b>',$offset)+4;
				if(($end=mb_strpos($text,'.',($marked_end+50<$n ? $marked_end+50 : $n)))===false)
					$end=$n;
				
				$segments[]=[$begin,$offset,$marked_end,$end];
				
				$offset=$marked_end;
			}
			
			$context=70;
			$concordances=[];
			foreach($segments AS $s)
			{
				$concordances[]=[
					strip_tags(mb_substr($text,max(0,$s[1]-$context),min($s[1],$context))),
					strip_tags(mb_substr($text,$s[1],$s[2]-$s[1])),
					strip_tags(mb_substr($text,$s[2],min($context,$n-$s[2])))
				];
			}
			$doc->concordances=$concordances;
			
			$marked_text='';
			$beg=-1;
			$end=-1;
			foreach($segments AS $s)
			{
				if($end>=$s[0])
				{
					$end=$s[3];
				}
				else
				{
					if($beg!=-1) $marked_text.=' ... '.mb_substr($text,$beg,$end-$beg);
					$beg=$s[0];
					$end=$s[3];
				}
			}
			if($beg!=-1) $marked_text.=' ... '.mb_substr($text,$beg,$end-$beg);
			if($marked_text)
				$doc->snippet=$marked_text.' ...';
			else
				$doc->snippet=mb_substr($doc->text,0,500);
			
		}
		
		return ['results'=>$docs, 'statistics'=>$this->getStatistics($searchResponse), 'hits'=>$searchResponse->getHits()];
	}
	
	function getContext($req)
	{
		$q=[];
		$q['type']='speech';
		$q['source']=$req['source'];
		$q['sequence']=($req['sequence']-3).' .. '.($req['sequence']+3);
		
		$query='{('.CPS_QueryArray($q).')('.CPS_QueryArray($q).CPS_Term($this->parese_query($req['query']),'text').')}';
		// var_dump($query);
		$cpsSearch = new CPS_SearchRequest($query,0,100);
		$cpsSearch->setList([
			'text'=>'highlight'
		]);
		
		$cpsSearch->setOrdering(CPS_NumericOrdering('sequence','asc'));
		
		return $this->conn->sendRequest($cpsSearch)->getDocuments(DOC_TYPE_STDCLASS);
		
	}
	
}