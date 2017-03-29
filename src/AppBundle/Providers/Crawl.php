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
  public function CrawlOn($provider, $nbr = 2) {
    if ($provider == 'wandaloo') {
      $wandaloo = new Wandaloo($this->em, $this->sphinx);
      $wandaloo->fetchALLAnnonces($nbr);
    }

    if ($provider == 'sarouty') {
      $sarouty = new Sarouty($this->em, $this->sphinx);
      $sarouty->fetchALLAnnonces($nbr);
    }

    if ($provider == 'moteur') {
      $moteur = new Moteur($this->em, $this->sphinx);
      $moteur->fetchALLAnnonces(2);
    }

    if ($provider == 'marocannonces') {
      $wandaloo = new Marocannonces($this->em, $this->sphinx);
      $wandaloo->fetchALLAnnonces($nbr);
    }

    if ($provider == 'avitoma') {
      $avito = new Avitoma($this->em, $this->sphinx);
      $avito->fetchALLAnnonces($nbr);
    }

    if ($provider == 'soukma') {
      $avito = new Souk($this->em, $this->sphinx);
      $avito->fetchALLAnnonces($nbr);
    }

    if ($provider == 'voituresaumaroc') {
      $voitureaumaroc = new Voituresaumaroc($this->em, $this->sphinx);
      $voitureaumaroc->fetchALLAnnonces($nbr);
    }
  }
}
