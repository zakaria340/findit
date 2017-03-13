<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags
 *
 * @ORM\Table(name="tags")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagsRepository")
 */
class Tags {

  /**
   * @var int
   *
   * @ORM\Column(name="idTags", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */

  private $idTags;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=255)
   */

  private $name;


  /**
   * @var string
   *
   * @ORM\Column(name="slug", type="string", length=255)
   */

  private $slug;
}