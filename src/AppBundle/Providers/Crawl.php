<?php

namespace AppBundle\Providers;

use AppBundle\Providers\Moteur;
use AppBundle\Providers\Sarouty;
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
    $sarouty = new Sarouty($this->em, $this->sphinx);
    $sarouty->fetchALLAnnonces();
    var_dump($sarouty);
    die;
    //$moteur->fetchALLAnnonces(2);

  }
}
