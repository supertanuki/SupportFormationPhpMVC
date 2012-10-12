<html>
	<head>
		<title>Mon blog</title>
	</head>
	<body>
		<h1>Mon 1er blog PHP</h1>
		<hr />
		
		<a href="?afficher-commentaires">Afficher les derniers commentaires</a>
		
		<hr />
		
		<?php
		if(is_array($billets)) {
			foreach($billets as $billet)
			{
				?>
				<h2><a href="?billet=<?php echo $billet['id']; ?>"><?php echo htmlspecialchars( $billet['titre'] ); ?></a></h2>
				<div>
					<?php echo nl2br( htmlspecialchars( $billet['contenu'] )); ?>
				</div>
				
				<p><i>Publié le <?php echo htmlspecialchars( $billet['date_creation_fr'] ); ?></i></p>
				
				<p>
					<?php echo $billet['nb_commentaires']; ?>
					commentaires
				</p>
				
				<hr />
				<?php
			}
		}
		?>
		
		<?php if( isset($pages) ) { ?>
			<div class="pagination">
				<?php foreach ($pages as $key){ ?>
					<?php
					// page courante
					if($key['current'] == 1) { ?>
						<strong style="color:red"><?php echo $key['page']?></strong>
					<?php
					// autres pages
					} else { ?>
						<a href="?p=<?php echo $key['p']; ?>"><?php echo $key['page']?></a>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>
	</body>
</html>