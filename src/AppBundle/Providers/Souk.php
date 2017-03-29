<?php

namespace AppBundle\Providers;

use Sunra\PhpSimple\HtmlDomParser;

Class Souk {

  public $_baseUrl = '';
  protected $em;
  protected $sphinx;

  public function __construct($em, $sphinx) {
    $this->em = $em;
    $this->sphinx = $sphinx;
  }

  public function getData($url, $data) {
    $s = preg_match_all('/fr\/(.*)_/', $url, $matches);
    $annonceID = $matches[1][0];

    /**
     * Check if Annonce exist.
     */
    $d = $this->sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }

    $html = HtmlDomParser::file_get_html($url);
    $dataToSave = array();

    if ($html && $html->find('h1', 0)) {
      $prix = $image = $title = $urlAnnonces = '';
      $title = $html->find('h1', 0)->plaintext;
      $title = strip_tags($title);
      $title = trim($title);
      $date = $html->find('.annonce .date span', 0)->plaintext;
      $date = str_replace('Publié le: ', '', $date);
      $date = str_replace('/', '-', $date);
      $date = strtotime($date);

      if ($html->find('.annonce .price span', 0)) {
        $prix = trim($html->find('.annonce .price span', 0)->plaintext);
        $prix = str_replace('Dhs', '', $prix);
        $prix = str_replace('DH', '', $prix);
        $prix = trim($prix);
      }
      if ($html->find('.annonce .adphoto img', 0)) {
        $image = trim($html->find('.annonce .adphoto img', 0)->src);
        $imageUnique = md5(time() . 6 . $annonceID) . '.jpg';
        $pathNewImage = $this->sphinx->resizeandsave($image, $data->getIdSites(), $imageUnique);
      } else {
        return array();
      }
      if ($html->find('.annonce .date span', 1)) {
        $ville = $html->find('.annonce .date span', 1)->plaintext;
        $ville = trim($ville);
        $ville = explode(' ', $ville);
        $ville = trim($ville[0]);
        $idVille = $this->sphinx->getVille(trim($ville));

      }
      if ($html->find('.breadcrumb li', 2)) {
        $tag = trim($html->find('.breadcrumb li', 2)->plaintext);
        if($tag == 'Appartements') {
          $tag = 'Appartement';
        }
        $tags = $this->sphinx->getTags(array($tag));
      }
      $extraKeywords = array();
      foreach ($html->find('#colonne-gauche-bloc-annonce table tr') as $liinfo) {
        if ($liinfo->find('td', 1)) {
          $dataitem = array('label' => $liinfo->find('td', 0)->plaintext,
            'value' => trim($liinfo->find('td', 1)->plaintext));
          array_push($extraKeywords, $dataitem);
        }
      }
      $description = '';
      if ($html->find('.desc-text p', 0)) {
        $description = $html->find('.desc-text p', 0)->plaintext;
      }
      $dataToSave = array(
        'idSphinx' => $data->getPrefix() . $annonceID,
        'idAnnonce' => $annonceID,
        'idSite' => $data->getIdSites(),
        'title' => trim($title),
        'description' => trim($description),
        'date' => $date,
        'ville' => array($idVille => $ville),
        'tags' => $tags,
        'image' => $pathNewImage,
        'prix' => $prix,
        'url' => $url,
        'extraKeywords' => $extraKeywords
      );
    }
    return $dataToSave;
  }

  public function fetchALLAnnonces($nbr) {
    $site = $this->em->getRepository('AppBundle:Sites')->find(6);
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = HtmlDomParser::file_get_html('http://www.souk.ma/fr/Maroc/immobilier/&q=&period=semaine&p=' . $i);
      foreach ($listpagehtml->find('.results .item') as $item) {
        $link = $item->find('a', 0)->href;
        $dataToSave = $this->getData($link, $site);
        if (!empty($dataToSave)) {
          $this->sphinx->SaveToSphinx($dataToSave);
        } 
      }
    }
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = HtmlDomParser::file_get_html('http://www.souk.ma/fr/Maroc/immobilier/&q=&period=semaine&p=' . $i);
      foreach ($listpagehtml->find('.results .item') as $item) {
        $link = $item->find('a', 0)->href;
        $dataToSave = $this->getData($link, $site);
        if (!empty($dataToSave)) {
          $this->sphinx->SaveToSphinx($dataToSave);
        }
      }
    }
  }

}
