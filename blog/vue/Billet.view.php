<html>
	<head>
		<title>Mon blog</title>
	</head>
	<body>
		<div><a href="/">Mon 1er blog PHP</a></div>
		<hr />
		<?php
		include('vue/message.service.php');
		?>
		
		<?php
		if(is_array($oneBillet)) {
			foreach($oneBillet as $billet)
			{
				?>
				<h1><?php echo htmlspecialchars( $billet['titre'] ); ?></h1>
				<div>
					<?php echo nl2br( htmlspecialchars( $billet['contenu'] )); ?>
				</div>
				
				<p><i>Publié le <?php echo htmlspecialchars( $billet['date_creation_fr'] ); ?></i></p>
				
				<p>
					<?php
					if( is_array($billet['commentaires']) ) {
						echo count( $billet['commentaires'] );
					} else {
						echo 0;
					}
					?>
					commentaires
				</p>
				
				<hr />
				
				<?php
				if( is_array($billet['commentaires']) ) {
					?>
					<ol>
					<?php
					foreach( $billet['commentaires'] as $commentaire ) {
						?>
						<li>
							<strong><?php echo htmlspecialchars( $commentaire['auteur'] ); ?></strong> 
							<i>a écrit le <?php echo htmlspecialchars( $commentaire['date_message_fr'] ); ?> :</i><br />
							<?php echo nl2br( htmlspecialchars( $commentaire['message'] ) ); ?>
							<br />&nbsp;
						</li>
						<?php
					}
					?>
					</ol>
					<?php
				}
				?>
				
				<form method="post" action="">
					<fieldset>
						<legend>Ajouter un commentaire</legend>
					
						Votre nom :<br />
						<input type="text" name="auteur" /><br /><br />
					
						Votre message :<br />
						<textarea name="message" rows="5" cols="30"></textarea><br /><br />
						
						<input type="submit" value="Propulser le commentaire" />
					</fieldset>
				</form>
				
				<?php
			}
		}
		?>

		<p><a href="/">Retour à la liste des billets</a></p>
	</body>
</html>