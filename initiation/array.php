<?php
function mon_var_dump($array)
{
	if(is_array($array))
	{
		echo '<ul>';
		foreach($array as $key => $value)
		{
			echo '<li>';
			echo $key;
			echo '=';
			mon_var_dump($value);
			echo '</li>';
		}
		echo '</ul>';
	} else {
		echo $array;
	}
}

$montableau = array(
				'nom' 		=> 'Hanna',
				'prenom' 	=> 'Richard',
				'hobbies' 	=> array(array('hello'), 'cinema', 'petanque'),
				'tags' => array(
					'couleur' => array(125, "getetejhjkhzej"),
					)
				);
				
mon_var_dump($montableau);







$billets = array(
				0 => array(
						'id' => 1,
						'titre' => 'lorem ipsum',
						'nb_commentaire' => 2
					),
				1 => array(
						'id' => 1,
						'titre' => 'lorem ipsum',
						'nb_commentaire' => 0
					)
				);









