<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

if ( ! function_exists('page_string'))
{
	/*funkcija atgriež stringu ar noformāteu lapas izvēli
	 * $tot = kopējais ierakstu sakits
	 * $lpp = ierakstu skaits vienā lapā
	 * $pg = aktīvā lapa (sākot numurēt no 0 nevis 1)
	 * $urlbase = adrese, kur %lpp% jāaizstāj ar lpp numuru
	 */
	function page_string($tot,$lpp,$pg,$urlbase)
	{
		$pg++;
		$lapas=ceil($tot/$lpp);
		$l='<nav><ul class="pagination">';
		
		if($pg==1)
		{
			$l.=' <li class="disabled"><a>«</a></li>';
		}
		else
		{
			$l.=' <li><a href="'.str_replace("%lpp%",($pg-2),$urlbase).'">«</a></li>';
		}
		$l.='<li class="'.( ($pg==1) ? "active" : "").'"><a href="'.str_replace("%lpp%",0,$urlbase).'">1</a></li>';
		
		if($pg>3)
		{
			$i=$pg-3;
			$j=$pg+8;
			if($i>2)
				$l.='<li class="disabled"><a>...</a></li>';
		}
		else 
		{
			$i=2;
			$j=15;
		}
		while($i<$j && $i<$lapas)
		{
			$l.='<li class="'.( ($pg==$i) ? "active" : "").'"><a href="'.str_replace("%lpp%",($i-1),$urlbase).'">'.$i.'</a></li>';
			$i++;
		}
		if($lapas>1)
		{
			if($i<$lapas)
				$l.='<li class="disabled"><a>...</a></li>';
			$l.='<li class="'.( ($pg==$lapas) ? "active" : "").'"><a href="'.str_replace("%lpp%",($lapas-1),$urlbase).'">'.$lapas.'</a></li>';
		}
		
		if($pg==$lapas)
		{
			$l.=' <li class="disabled"><a>»</a></li>';
		}
		else
		{
			$l.=' <li><a href="'.str_replace("%lpp%",($pg),$urlbase).'">»</a></li>';
		}
		
		$l.="</ul></nav>";
		return $l;
	}
}
