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
 
 <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
 <p><input type="file" name="image[]" /></p>
 <p><input type="file" name="image[]" /></p>
 <p><input type="file" name="image[]" /></p>
 
 <p><input type="submit" value="OK"></p>
</form>

<?php
// var_dump( $_POST );
var_dump( $_FILES );

function getNormalizedFILES() 
{ 
    $newfiles = array(); 
    foreach($_FILES as $fieldname => $fieldvalue) 
        foreach($fieldvalue as $paramname => $paramvalue) 
            foreach((array)$paramvalue as $index => $value) 
                $newfiles[$fieldname][$index][$paramname] = $value; 
    return $newfiles; 
}

var_dump(getNormalizedFILES());


/*
$uploaddir = 'upload';
$uploadfile = $uploaddir . '/' . basename($_FILES['image']['name']);

if (is_uploaded_file($_FILES['image']['tmp_name'])) {
	if($_FILES['image']['size'] > 2000000)
	{
		echo 'La taille du fichier dépasse 2 Mo !';
		
	} else {
		if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
			echo "Le fichier a été uploadé.";
		} else {
			echo "Le téléchargement a échoué.";
		}
	}
} else {
	echo "Aucun fichier n'a été chargé.";
}
*/