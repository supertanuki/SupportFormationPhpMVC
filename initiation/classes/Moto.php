<?php
class Moto extends Voiture {
	
	public function __construct($marque = 'Renault')
	{
		parent::__construct($marque);
	}
	
	public function leCasqueEstIlFourni()
	{
		if($this->marque == 'BMW') return true;
		
		return false;
	}
	
	public function setNbPortes($portes)
	{
		parent::setNbPortes(NULL);
		
		echo '<p>impossible de mettre des portes sur une moto !</p>';
	}
	
}