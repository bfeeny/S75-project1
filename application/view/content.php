		<div class='container'>
       		<div class='page-header'>
	       		<?php echo "<h1>$pageTitle</h1>"; ?>
	       		<div class='pull-right'>
		       		<?php echo "$loginStatus"; ?> 
		       	</div><!--pull-right-->	
	       	</div><!--page-header-->
	          		         
	       	<div class='pull-left'>
	       		<?php echo "$displayBalance"; ?>
		        <?php echo "$statusMessage"; ?> 
		    </div><!--pull-left-->	
		    
		    <br />
		    
			<div class='well'>
				<?php echo $renderView; ?>
			</div><!--well-->
       	</div><!--container-->
       	
       