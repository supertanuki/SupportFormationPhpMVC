<html>
	<head>
		<title>Mon blog</title>
	</head>
	<body>
		<div>Mon 1er blog PHP</div>
		<hr />
		
		<?php
		if(is_array($billets)) {
			foreach($billets as $billet)
			{
				?>
				<h1><?php echo htmlspecialchars( $billet['titre'] ); ?></h1>
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
		
		<?php if( $afficher_retour_liste == true ) { ?>
			<p><a href="/">Retour à la liste des billets</a></p>
		<?php } ?>
	</body>
</html>