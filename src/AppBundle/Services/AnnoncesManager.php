<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use FS\SolrBundle\Doctrine\Hydration\HydrationModes;

Class AnnoncesManager {

  /**
   *
   * @var EntityManager
   */
  protected $em;

  protected $solr;

  /**
   * DataminerSoapClient constructor.
   *
   * @param EntityManagerInterface $em
   */
  public function __construct(EntityManagerInterface $em, $solr) {
    $this->em = $em;
    $this->solr = $solr;
  }

  /**
   *
   * @param EntityManagerInterface $em
   *
   * @return Response
   */
  public function getRandomAnnonces() {
    return $this->em->getRepository('AppBundle:Annonces')->RandomAnnonces(
      array('voitures', 'appartement', 'villa', 'ordinateurs-portables')
    );
  }

  /**
   * @param $ville
   * @param $tags
   * @param $keys
   * @return mixed
   */
  public function findAllPaginates($ville, $tags, $keys) {
    $query = $this->solr->createQuery('AppBundle:Annonces');
    $q = '';
    if ($ville != 'tous') {
      $q .= "*$ville*";
    }
    if ($tags != 'tous') {
      $q .= $q != '' ? ' AND' : '';
      $q .= "*$tags*";
    }
    if ($keys != '') {
      $q .= $q != '' ? ' AND' : '';
      $q .= "\"$keys\"";
    }
    $query->setCustomQuery($q);
    $result = $query->getResult();
    return $result;
  }

}
