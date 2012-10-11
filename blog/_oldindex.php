<?php
require_once('include/bdd.php');

require_once('classes/Pagination.php');
?>

<html>
	<head>
		<title>Mon blog</title>
	</head>
	<body>
		<h1>Mon 1er blog PHP</h1>
		<hr />
		
		<?php
		$nbbillet = 0;
		$reqCount = $database->query('
			SELECT COUNT(id) AS nb_billets
			FROM billets')
			or die( var_dump( $database->errorInfo() ));
		if($row = $reqCount->fetch( PDO::FETCH_ASSOC ))
		{
			$nbbillet = $row['nb_billets'];
		}
		
		$pagination = new pagination();
		$pagination->byPage = 2;
		$pagination->rows = $nbbillet;
		$from = $pagination->fromPagination();
		$pages = $pagination->pages();
		
		$req = $database->query('
			SELECT id, titre, contenu, DATE_FORMAT(date_creation, \'%d/%m/%Y à %Hh%imin%ss\') AS date_creation_fr
			FROM billets
			ORDER BY date_creation DESC
			LIMIT ' . $from . ', ' . $pagination->byPage)
			or die( var_dump( $database->errorInfo() ));
			
		while ($billet = $req->fetch( PDO::FETCH_ASSOC ))
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