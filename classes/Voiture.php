<?php
class Voiture {
	
	protected $marque;
	protected $nbportes;
	
	const CARBURANT_DIESEL = 1;
	const CARBURANT_ESSENCE = 2;
	const CARBURANT_ELECTRIQUE = 3;
	
	public function __construct($marque = 'Renault')
	{
		$this->setMarque( $marque );
		$this->affiche_quelquechose( "Création de : $marque" );
	}
	
	public function __destruct()
	{
		echo "<li>Fin de l'object ".$this->marque;
	}
	
	public function getMarque()
	{
		return $this->marque;
	}
	
	public function setMarque($marque)
	{
		$this->marque = $marque;
	}
	
	public function getNbPortes()
	{
		return $this->nbportes;
	}
	
	public function setNbPortes($nbportes)
	{
		$this->nbportes = $nbportes;
		$this->affiche_quelquechose( "Set nb de portes à ".$nbportes." pour ". $this->marque );
	}
	
	static function affiche_quelquechose($quelquechose)
	{
		echo "<p>Tu m'as demandé d'afficher : $quelquechose</p>";
	}
	
}