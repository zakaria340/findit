<?php

namespace AppBundle\Providers;


use Doctrine\ORM\EntityManager;

Class Crawl {

  /**
   *
   * @var EntityManager
   */
  protected $em;

  protected $sphinx;

  public function __construct(EntityManager $entityManager, $sphinx) {
    $this->em = $entityManager;
    $this->sphinx = $sphinx;
  }

  public function CrawlOn() {
    $wandaloo = new Marocannonces($this->em, $this->sphinx);
    $wandaloo->fetchALLAnnonces();
    var_dump($wandaloo);
    die;
    
    /*$sarouty = new Sarouty($this->em, $this->sphinx);
    $sarouty->fetchALLAnnonces();
    var_dump($sarouty);
    die;*/
    //$moteur->fetchALLAnnonces(2);

  }
}
