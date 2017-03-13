<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sites
 *
 * @ORM\Table(name="sites")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SitesRepository")
 */
class Sites {

  /**
   * @var int
   *
   * @ORM\Column(name="idSites", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  
  private $idSites;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=255)
   */
  
  private $name;


  /**
   * @var string
   *
   * @ORM\Column(name="idFetchsAll", type="integer", length=11)
   */
  
  private $idFetchsAll;

  /**
   * @var string
   *
   * @ORM\Column(name="lastIdInsertId", type="integer", length=11)
   */
  private $lastIdInsertId;

  /**
   * @var string
   *
   * @ORM\Column(name="prefix", type="integer", length=11)
   */
  private $prefix;

  /**
   * @var string
   *
   * @ORM\Column(name="logo", type="string", length=255)
   */
  private $logo;
  
}
