<?php

namespace AppBundle\Providers;

use Doctrine\ORM\EntityManager;

/**
 * Class Crawl
 *
 * @package AppBundle\Providers
 */
Class Crawl {

  /**
   * @var EntityManager
   */
  protected $em;

  /**
   * @var Sphinx
   */
  protected $sphinx;

  /**
   * Crawl constructor.
   *
   * @param \Doctrine\ORM\EntityManager $entityManager
   * @param $sphinx
   */
  public function __construct(EntityManager $entityManager, $sphinx) {
    $this->em = $entityManager;
    $this->sphinx = $sphinx;
  }

  /**
   * Crawl ON.
   * 
   * @see AppBundle:Cron.
   */
  public function CrawlOn() {
    $wandaloo = new Marocannonces($this->em, $this->sphinx);
    $wandaloo->fetchALLAnnonces(2);

    $sarouty = new Sarouty($this->em, $this->sphinx);
    $sarouty->fetchALLAnnonces(2);

    $wandaloo = new Wandaloo($this->em, $this->sphinx);
    $wandaloo->fetchALLAnnonces(2);

    $moteur = new Moteur($this->em, $this->sphinx);
    $moteur->fetchALLAnnonces(2);

    $avito = new Avitoma($this->em, $this->sphinx);
    $avito->fetchALLAnnonces(2);

  }
}
