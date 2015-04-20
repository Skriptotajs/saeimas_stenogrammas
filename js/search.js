$(function(){
	
	
	$("#result_type").on('click','label',function(){
		$.cookie('result_type', $(this).attr("data-value"), { expires: 365 });
		$("#result_type").find(".active").removeClass("active");
		$(this).addClass("active");
		$("#results").attr("class",$(this).attr("data-value"));
	});

	if(typeof $.cookie('result_type')=='undefined')
		$.cookie('result_type', 'snippet', { expires: 365 });
	$("#"+$.cookie('result_type')).click();
	
	function createTag(i)
	{
		var full_path=advanced_params[i].name;
		var short_name=(advanced_params[i].name.length > 26 ? advanced_params[i].name.substr(0,23)+'...' : advanced_params[i].name);
		var parent=advanced_params[i].parent;
		while(parent!=-1)
		{
			full_path=advanced_params[parent].name + '|' + full_path;
			short_name+=' | '+(advanced_params[parent].name.length > 26 ? advanced_params[parent].name.substr(0,23)+'...' : advanced_params[parent].name);
			parent=advanced_params[parent].parent
		}
		short_name=short_name.replace(/_/g,' ');
		advanced_params[i].checkbox.prop('checked',true);
		advanced_params[i].tag=$('<span data-index="'+i+'" class="tag"><input type="hidden" name="params[]" value="'+full_path+'" />'+short_name+' <span class="close" aria-label="Close"><span aria-hidden="true">&times;</span></span></span>')
									.appendTo("#tags");
	}

	function markChilds(parent,checked)
	{
		var level=advanced_params[parent].level;
		for(var j=parent+1 ; j<advanced_params.length && advanced_params[j].level > level ; j++)
		{
			advanced_params[j].checkbox.prop('checked',checked);
			removeTag(j);
		}
	}
	
	function removeTag(i)
	{
		if(advanced_params[i].tag==false) return;
		advanced_params[i].tag.remove();
		advanced_params[i].tag=false;		
	} 
	
	var html='';
	prev_level=-1;
	for(var i in advanced_params)
	{ 
		if(prev_level==advanced_params[i].level)
		{
			html+='</li>';
			prev_level=advanced_params[i].level;
		}
		
		
		if(prev_level<advanced_params[i].level)
		{
			html+='<ul '+(advanced_params[i].level>0 ? 'style="display:none"' : '') +'>';
			prev_level=advanced_params[i].level;
		}
		
		while(prev_level>advanced_params[i].level)
		{
			html+='</ul></li>';
			prev_level--;
		}
		
		html+='<li id="params'+i+'"> <span>'+advanced_params[i].long_name+'</span>';	
	}
	
	while(prev_level>0)
	{
		html+='</ul></li>';
		prev_level--;
	}
	
	html+='</ul>';

	$('#advancedParams').html(html);
	
	for(var i in advanced_params)
	{
		advanced_params[i].elem=$("#params"+i);
		advanced_params[i].checkbox=$('<input type="checkbox" class="checkbox" value="'+i+'" />').prependTo(advanced_params[i].elem);
		advanced_params[i].tag=false;
	}

	for(var i in advanced_params)
	{
		i=parseInt(i);
		if(advanced_params[i].checked)
		{
			createTag(i);
			markChilds(i,true);
		}

		delete advanced_params[i].checked;
	}
	
	$("#tags").on('click','.close',function(){
		var i=$(this).closest('.tag').attr('data-index');
		advanced_params[i].checkbox.prop('checked',false).trigger('change');
		
	});
	
	function isFull(parent)
	{
		var level=advanced_params[parent].level;
		for(var i=parent+1 ; i<advanced_params.length && advanced_params[i].level > level ; i++)
		{
			if(advanced_params[i].checkbox.prop('checked')==false)
				return false;
		}
		
		return true;
	}
	
	$('#advancedParams').on('change','.checkbox',function(){
		var i=parseInt($(this).val());
		var parent=advanced_params[i].parent;
		
		if($(this).prop('checked'))
		{
			createTag(i);
			
			markChilds(i,true);
			
			var prev_parent=-1;
			while(parent!=-1 && isFull(parent))
			{
				prev_parent=parent;
				advanced_params[parent].checkbox.prop('checked',true);
				parent=advanced_params[parent].parent;
			}
			
			if(prev_parent!=-1)
			{
				createTag(prev_parent);
				var level=advanced_params[prev_parent].level;
				for(var i=prev_parent+1 ; i<advanced_params.length && advanced_params[i].level > level ; i++)
				{
					removeTag(i);
				}
			}
		}
		else
		{
			removeTag(i);
			
			markChilds(i,false);
			
			var prev_parent=-1;
			while(parent!=-1 && advanced_params[parent].checkbox.prop('checked'))
			{
				removeTag(parent);
				advanced_params[parent].checkbox.prop('checked',false);
				prev_parent=parent;
				parent=advanced_params[parent].parent;
			}
			if(prev_parent!=-1)
			{
				level=advanced_params[prev_parent].level;
				var skip_level=1000;
				for(var i=prev_parent+1 ; i<advanced_params.length && advanced_params[i].level > level ; i++)
				{
					if(advanced_params[i].level>skip_level)
					{
						continue;
					}
					if(advanced_params[i].checkbox.prop('checked'))
					{
						createTag(i);
						skip_level=advanced_params[i].level;
					}
					else
					{
						skip_level=1000;
					}
				}
			}
			
		}
		
	});

	$("#searchValue").on("keyup",function(){
		var value=$(this).val().trim();
		var $groups=$("#advancedParams").children('ul');
		if(value.length==0)
		{
			$groups.find('li').show();
			$groups.find("ul").hide();
			$groups.children('li').show();
		}
		else if(value.length>=4)
		{
			$groups.find('li,ul').hide();
			var regexp=new RegExp(value, "i");
			matches=0;
			for(var i in advanced_params)
			{
				if(advanced_params[i].name.match(new RegExp(value, "i")))
				{
					advanced_params[i].elem.show();
					advanced_params[i].elem.parents("li,ul").show();
					matches++;
				}
			}
		}
	});


	
	$(".checbox_wrapper").on("click","span",function(){
		$(this).closest('li').children('ul').slideToggle();
	});
	
	
	$("#periods").change(function(){
		var $option=$(this).find("option:selected");
		if($option.attr('data-from'))
		{
			$("#date_from").datepicker( "setDate",new Date($option.attr('data-from')) );
			$("#date_to").datepicker( "setDate",new Date($option.attr('data-to')) );
		}
	});
	
	$( "#date_from" ).datepicker({
      changeMonth: true,
      changeYear: true,
      changeYear: true,
	  showOtherMonths: true,
      selectOtherMonths: true,
	  dateFormat: 'yy/mm/dd',
	  minDate: calendar_min,
	  maxDate: calendar_max,
      onClose: function( selectedDate ) {
        $( "#date_to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#date_to" ).datepicker({
      changeMonth: true,
      changeYear: true,
      changeYear: true,
	  showOtherMonths: true,
      selectOtherMonths: true,
	  dateFormat: 'yy/mm/dd',
	  minDate: calendar_min,
	  maxDate: calendar_max,
      onClose: function( selectedDate ) {
        $( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	
	$("#filter_button").click(function(){
		
		if($("#filter").is(':visible'))
		{
			$("#tags").insertBefore("#filter");
		}
		else
		{
			$("#tags").appendTo("#tags_container");
		}

		$("#filter").slideToggle();

	});
	
	$("#results")
		.on('mouseenter','.turn',function(){
			var $card=$("#card");
			var $turn=$(this);
			$card.hide();
			$card.find("img").attr("src",$turn.attr("data-img"));
			$card.find(".name").text($turn.attr("data-name"));
			$card.find(".role").text($turn.attr("data-role"));
			$card.find(".date").text($turn.attr("data-date"));
			var pos=$turn.position();
			$card.css({
				'top':(pos.top-80)+'px',
				'left':pos.left+'px'
			});
			$card.show();
		})
		.on('mouseleave',function(){
			$("#card").hide();
		})
		.on('click','.turn',function(){
			var $turn=$(this);
			$.get(site_url+'/search/context/',{sequence:$turn.attr('data-sequence'),source:$turn.attr('data-source'),query:$("#text").val()},function(resp){
				var $modal=$("#turn-modal");
				$modal.find('.modal-body').html(resp);
				$modal.find('.modal-title').text($turn.attr('data-date'));
				$modal.modal('show');
				
			});
		});

		$( "#search_form" ).submit(function( event ) {
		  var data=$(this).serialize();
		  $.post(site_url+'/search/get_result_url',data,function(resp){
			window.location.href=resp;
		  });
		  event.preventDefault();
		});

		$("#help").click(function(){
			$("#help-modal").modal('show');
		});
});