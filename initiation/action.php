<form action="" method="post" enctype="multipart/form-data">
 <p>Votre nom :
 <input type="text" name="nom" value="<?php echo isset($_GET['nom']) ? htmlspecialchars( $_GET['nom'] ) : ''; ?>" />
 </p>
 <p>Votre âge :
 <input type="text" name="age" value="<?php echo isset($_GET['age']) ? htmlspecialchars( $_GET['age'] ) : ''; ?>" />
 </p>
 <p>
 <input type="checkbox" name="couleur[]" value="vert"> Vert<br />
 <input type="checkbox" name="couleur[]" value="bleu"> bleu<br />
 <input type="checkbox" name="couleur[]" value="rouge"> rouge<br />
 </p>
  <!--<p>Votre message :
 <textarea name="message" rows="5"><?php echo isset($_GET['message']) ? htmlspecialchars( $_GET['message'] ) : ''; ?></textarea>
 </p>-->
 
 <p><input type="file" name="image" /></p>
 
 <p><input type="submit" value="OK"></p>
</form>

<?php
var_dump( $_GET );








/*
if ( isset( $_GET['nom'] )
	&& isset( $_GET['age'] )
	&& strlen( $_GET['nom'] ) > 2
	&& is_integer( (int) $_GET['age'] )
	) {

	echo htmlspecialchars($_GET['nom'] . ' ('. $_GET['age'] . ')');
	echo '<p>';
	echo nl2br( htmlspecialchars( strip_tags( $_GET['message'] ) ) );
}
*/
?>