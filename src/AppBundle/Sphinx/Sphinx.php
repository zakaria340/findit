<?php

namespace AppBundle\Sphinx;

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;

use AppBundle\Entity\Villes;
use AppBundle\Entity\Tags;
use AppBundle\Entity\TagsAnnonces;
use AppBundle\Entity\Annonces;
use AppBundle\Entity\TagsExtra;

Class Sphinx {

  /**
   *
   * @var EntityManager
   */
  protected $em;

  protected $rootDir;

  protected $conxSphinx;

  protected $tableSphinx;

  /**
   * Sphinx constructor.
   *
   * @param $em
   * @param $rootDir
   */
  public function __construct($em, $rootDir) {
    $this->em = $em;
    $this->rootDir = realpath($rootDir . '/../web/images');
    $conn = new Connection();
    $conn->setParams(array('host' => '127.0.0.1', 'port' => 9306));
    $this->conxSphinx = $conn;
    $this->tableSphinx = 'annonces11';
  }

  /**
   * @param $url
   *
   * @return mixed
   */
  public function checkAnnoncebyUrl($url) {
    $annonce = $this->em->getRepository('AppBundle:Annonces')->findOneBy(array('url' => $url));
    return $annonce;
  }

  /**
   * @param $data
   */
  public function SaveToSphinx($data) {
    $ville = array_values($data['ville']);
    $ville = $ville[0];

    $rawExtraKeywords = $data['extraKeywords'];

    $annonce = new Annonces();
    $annonce->setIdSite($data['idSite']);
    $annonce->setTitle($data['title']);
    $annonce->setDescription($data['description']);
    $annonce->setDate($data['date']);
    $annonce->setVille($ville);
    $annonce->setTags(implode(', ', $data['tags']));
    $annonce->setImage($data['image']);
    $annonce->setPrix($data['prix']);
    $annonce->setUrl($data['url']);
    $annonce->setExtraKeywords(json_encode($data['extraKeywords']));
    $this->em->persist($annonce);
    $this->em->flush();
    $idAnnonce = $annonce->getId();
die('aze');

    /*
     * Save extra keywords.
     */
    $this->getTagsAnnonces($data['extraKeywords'], $idAnnonce);

    /**
     * Sphinx Insert
     */
    $sq = SphinxQL::create($this->conxSphinx)->insert()->into($this->tableSphinx);

    $sphinxData = array(
      'id'            => $idAnnonce,
      'title'         => $data['title'],
      'description'   => $data['description'],
      'tags'          => implode(', ', $data['tags']),
      'extrakeywords' => json_encode($rawExtraKeywords),
      'idsite'        => $data['idSite'],
      'ville'         => $ville,
      'date'          => $data['date'],
    );
    $sq->set($sphinxData)->execute();
  }

  /**
   * @param $ville
   *
   * @return int
   */
  public function getVille($villeString) {
    $cleanString = $this->clean($villeString);
    $villeEntity = $this->em->getRepository('AppBundle:Villes')->findOneBy(array('slug' => $cleanString));
    if (is_null($villeEntity)) {
      $ville = new Villes();
      $ville->setName($villeString);
      $ville->setSlug($cleanString);
      $this->em->persist($ville);
      $this->em->flush();
      return $ville->getIdVilles();
    }

    return $villeEntity->getIdVilles();
  }


  /**
   * @param $tags
   *
   * @return array
   */
  public function getTags($tags) {
    $tagRepo = $this->em->getRepository('AppBundle:Tags');
    $listTags = array();

    foreach ($tags as $tag) {
      $cleanString = $this->clean($tag);
      $tagEntity = $tagRepo->findOneBy(array('slug' => $cleanString));
      if (!$tagEntity) {
        $tagEntity = new Tags();
        $tagEntity->setName($tag);
        $tagEntity->setSlug($cleanString);
        $this->em->persist($tagEntity);
        $this->em->flush();
      }
      $listTags[$tagEntity->getIdTags()] = $tag;
    }

    return $listTags;
  }

  /**
   * @param $tagsExtra
   * @param $idAnnonce
   *
   * @return bool
   */
  public function getTagsAnnonces($tagsExtra, $idAnnonce) {

    $tagAnnonces = $this->em->getRepository('AppBundle:TagsAnnonces');
    $tagsExtraEntity = $this->em->getRepository('AppBundle:TagsExtra');
    foreach ($tagsExtra as $tag) {
      if(isset($tag['label']) && isset($tag['value'])) {
        $cleanString = $this->clean(trim($tag['label']));
        $tagExtra = $tagsExtraEntity->findOneBy(array('slug' => $cleanString));
        if (!$tagExtra) {
          $tagExtra = new TagsExtra();
          $tagExtra->setSlug($cleanString);
          $tagExtra->setTitle(trim($tag['label']));
          $this->em->persist($tagExtra);
          $this->em->flush();
        }

        $tagAnnonce = new TagsAnnonces();
        $tagAnnonce->setIdAnnonce($idAnnonce);
        $tagAnnonce->setIdTags($tagExtra);
        $tagAnnonce->setValue($tag['value']);
        $this->em->persist($tagAnnonce);
        $this->em->flush();
      }
    }
    return TRUE;
  }

  /**
   * @param $images
   * @param $idSite
   * @param $imageUnique
   *
   * @return string
   */
  public function resizeandsave($images, $idSite, $imageUnique) {
    $width = 650;
    $size = GetimageSize($images);
    $height = round($width * $size[1] / $size[0]);
    $images_orig = ImageCreateFromJPEG($images);
    $photoX = ImagesX($images_orig);
    $photoY = ImagesY($images_orig);
    $images_fin = ImageCreateTrueColor($width, $height);
    ImageCopyResampled(
      $images_fin, $images_orig, 0, 0, 0, 0, $width + 1, $height + 1, $photoX,
      $photoY
    );
    
    $pathNewImage = $this->rootDir . '/' . $idSite . '/' . $imageUnique;
    ImageJPEG($images_fin, $pathNewImage);
    ImageDestroy($images_orig);
    ImageDestroy($images_fin);

    $pathNewImage = '/images/' . $idSite . '/' . $imageUnique;
    return $pathNewImage;
  }

  /**
   * @param $string
   *
   * @return mixed|string
   */
  public function clean($string) {
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
    $string = str_replace($a, $b, $string);
    $string = strtolower($string);
    $string = addslashes($string);
    $string = str_replace(' ', '-', $string);
    return $string;
  }

}
