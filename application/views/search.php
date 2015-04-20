<form id="search_form" method="POST">
	<div class="row">
		<div class="form-group col-md-4">
			<label for="text">Teksts</label>
			<div class="input-group">
				<input type="text" class="form-control" name="query" id="text" placeholder="Vaicājums" value="<?php echo isset($request['query']) ? htmlspecialchars($request['query']): '';?>">
				<span class="input-group-addon" id="help">
					<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
				</span>
			</div>
		</div>
		<div class="form-group col-md-2">
			<label for="text">No</label>
			<input type="text" class="form-control date_interval" name="date[from]" id="date_from" placeholder="Datums"  
				value="<?php echo isset($request['date']) ? $request['date']['from'] : '';?>"
				/>
		</div>
		<div class="form-group col-md-2">
			<label for="text">Līdz</label>
			<input type="text" class="form-control date_interval" name="date[to]" id="date_to" placeholder="Datums"  
				value="<?php echo isset($request['date']) ? $request['date']['to'] : '';?>"
				/>
		</div>
		<div class="form-group col-md-4">
			<label for="text">Periods</label>
			<select id="periods" class="form-control">
				<option>...</option>
				<?php foreach($periods AS $group=>$items): ?>
					<optgroup label="<?php echo $group;?>">
					<?php foreach($items AS $name=>$interval): ?>
						<?php if($interval[0]>$date['to'] || $interval[1]<$date['from']) continue;?>
						<option data-from="<?php echo $interval[0];?>" data-to="<?php echo $interval[1];?>"
							<?php echo (isset($request['date']) && $interval[0]==$request['date']['from'] && $interval[1]==$request['date']['to']) ? 'selected' : '';?>>
							<?php echo $name;?>
						</option>
					<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<label>Sēdes tips</label><br />
			<div class="btn-group" role="group">
				<?php $session_type=isset($request['session_type']) || !isset($result) ? $global_statistics['session_type'] : $result['statistics']['session_type'];?>
				<?php foreach($session_type AS $type):?>
					<label class="btn btn-default">
						<input type="checkbox" name="session_type[]" value="<?php echo $type;?>" 
							<?php echo isset($request['session_type']) && in_array($type,$request['session_type']) ? 'checked' : '';?> /> 
					<?php echo $type;?></label></button>
				<?php endforeach;?>
			</div>
		</div>
		<div class="form-group col-md-3">
			<label for="text">Kārtot pēc</label>
			<?php echo form_dropdown('order', ['relevence'=>'Atbilstības','date_desc'=>'Datuma dilstoši','date_asc'=>'Datuma pieaugoši'],isset($request['order']) ? $request['order'] : 'relevence','class="form-control"');?>
		</div>
		
		<div class="col-md-3 text-right">
			<label>&nbsp;</label><br />
			<a class="btn btn-danger" href="./">Dzēst</a>
			<div class="btn btn-default" id="filter_button">Vairāk opciju</div>
			<button type="submit" class="btn btn-primary">Meklēt</button>
		</div>
	</div>
	
	<div id="tags"></div>
	
	<div id="filter" style="display:none">
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="input-group">
							<input type="text" class="form-control" value="" id="searchValue" />
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
							</span>
						</div>
					</div>
					<div class="panel-body">
						<div class="checbox_wrapper" id="advancedParams"></div>
					</div>
				</div>
			</div>
			<div class="col-md-6" id="tags_container">
			</div>
		</div>
	</div>

</form>
<hr />

<div id="results">
<?php if(isset($result)): ?>
	<h2>Rezultāts 
		<div id="result_type" class="btn-group" role="group" aria-label="...">
		  <label id="snippet" class="btn btn-default active" data-value="snippet">Fragmenti</label>
		  <label id="concordances" class="btn btn-default" data-value="concordances">Konkordances</label>
		</div>
	</h2>
	<?php if(!$result['results']): ?>
		<div class="alert alert-danger" role="alert">
		  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
		  <span class="sr-only">Kļūda:</span>
		  Nekas netika atrasts!
		</div>
	<?php endif; ?>
	<?php foreach($result['results'] AS $r): ?>
	<?php
		$speaker=$speakers[$r->speaker];
	?>
	<div 
		class="turn" 
		data-source="<?php echo $r->source; ?>"
		data-sequence="<?php echo $r->sequence; ?>"
		data-img="<?php echo isset($speaker->picture) ? $speaker->picture : base_url('css/noimage.png')?>" 
		data-name="<?php echo (isset($speaker->name) ? $speaker->name : $speaker->role).(isset($speaker->year) ? ' ('.$speaker->year.')' : ''); ?>" 
		data-role="<?php echo $r->subcategory; ?>" 
		data-date="<?php echo $r->date.' '.(isset($r->session_name) ? $r->session_name : ''); ?>"
	>
			<div class="concordances">
			<?php foreach($r->concordances AS $c): ?>
				<div class="concordance">
					<div class="left">...<?php echo $c[0];?></div>
					<div class="center"><?php echo $c[1];?></div>
					<div class="right"><?php echo $c[2];?>...</div>
				</div>
			<?php endforeach;?>
			</div>
			<div class="snippet">
				<?php echo $r->snippet;?>...
			</div>
		
	</div>
	<?php endforeach;?>
	<?php echo $page_string;?>
<?php endif; ?>
</div>
<div id="card">
	<img src="" alt="" />
	<span class="name">test</span><br />
	<span class="role"></span><br />
	<span class="date"></span>
</div>
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="turn-modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Modal title</h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Aizvērt</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="help-modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Meklēšanas sintakse</h4>
      </div>
      <div class="modal-body">
	  <table class="table table-bordered" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
			<td colspan="2">
				Noklikšķinot uz rezultāta, tiek parādīts plašāks konteksts.
			</td>
			</tr>
			<tr>
				<td>
					māja
				</td>
				<td>
					vārds tiek meklēts dažādo tā locījumos
				</td>
			</tr>
			<tr>
				<td>
					māja puse
				</td>
				<td>
					vārdi tiek meklēti dažādos locījumos, ievērojot, ka tie atrodas viena runātāja runā (ne obligāti blakus)
				</td>
			</tr>
			<tr>
				<td>
					"māja"
				</td>
				<td>
					vārds tiek meklēts dotajā formā
				</td>
			</tr>
			<tr>
				<td>
					"mājas puse"
				</td>
				<td>
					frāze tiek meklēta dotajā formā
				</td>
			</tr>
			<tr>
				<td>
					med*
				</td>
				<td>
					meklē visus vārdus, kas sākas ar burtu kopu līdz simbolam *
				</td>
			</tr>
			<tr>
				<td>
					med???
				</td>
				<td>
					jautājuma zīme aizvieto jebkuru vienu simbolu
				</td>
			</tr>
			<tr>
				<td>
					atalgojums ~mediķi
				</td>
				<td>
					meklē doto, izņemot ar tildi apzīmēto vārdu
				</td>
			</tr>
			</tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Aizvērt</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
	var calendar_min=new Date("<?php echo $date['from'];?>");
	var calendar_max=new Date("<?php echo $date['to'];?>");
	var site_url= "<?php echo site_url();?>";
	var advanced_params=<?php echo json_encode($params);?>
</script>