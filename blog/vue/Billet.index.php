<html>
	<head>
		<title>Mon blog</title>
	</head>
	<body>
		<h1>Mon 1er blog PHP</h1>
		<hr />
		
		<?php
		if(is_array($billets)) {
			foreach($billets as $billet)
			{
				?>
				<h2><?php echo htmlspecialchars( $billet['titre'] ); ?></h2>
				<div>
					<?php echo nl2br( htmlspecialchars( $billet['contenu'] )); ?>
				</div>
				
				<p><i>Publié le <?php echo htmlspecialchars( $billet['date_creation_fr'] ); ?></i></p>
				<hr />
				<?php
			}
		}
		?>
		
		<?php if( isset($pages) ) { ?>
			<div class="pagination">
				<?php foreach ($pages as $key){ ?>
					<?php if($key['current'] == 1) { ?>
					<a href="?p=<?php echo $key['p']?>" class="active"><?php echo $key['page']?></a>
					<?php } else { ?>
					<a href="?p=<?php echo $key['p']; ?>" class="inactive"><?php echo $key['page']?></a>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>
		
	</body>
</html>