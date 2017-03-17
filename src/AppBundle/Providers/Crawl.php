<?php

namespace AppBundle\Providers;

use AppBundle\Providers\Moteur;
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
    $moteur = new Moteur($this->em, $this->sphinx);
    $moteur->fetchALLAnnonces();
    var_dump($moteur);
    die;
    //$moteur->fetchALLAnnonces(2);

  }
}
