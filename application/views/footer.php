	</div>
	
	<script src="<?php echo base_url("js/jquery.min.js");?>"></script>
	<script src="<?php echo base_url("js/bootstrap.min.js");?>"></script>

	<?php if(isset($js)): ?>
	<?php foreach($js AS $item):?>
	<script src="<?php echo base_url('js/'.$item.'.js');?>"></script>
	<?php endforeach; ?>
	<?php endif; ?>
</body>
</html>