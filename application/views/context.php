<?php foreach($context AS $r):?>
	<?php $speaker=$speakers[$r->speaker]; ?>
	<div class="turn">
	<div class="card">
		<img src="<?php echo isset($speaker->picture) ? $speaker->picture : base_url('css/noimage.png')?>" alt="" />
		<?php echo (isset($speaker->name) ? $speaker->name : $speaker->role).(isset($speaker->year) ? ' ('.$speaker->year.')' : ''); ?> 
		<br/>
		<?php echo $r->subcategory; ?> | <?php echo $r->category; ?>
	</div>
		<?php echo $r->text;?>
	</div>
<?php endforeach;?>