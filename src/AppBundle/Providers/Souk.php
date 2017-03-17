<?php

use Imagecow\Image;

Class Souk {

  public $_baseUrl = '';
  public $_db;

  public function __construct($db) {
    $this->_db = $db;
  }

  public function getData($url, $data) {
    $s = preg_match_all('/fr\/(.*)_/', $url, $matches);
    $annonceID = $matches[1][0];

    $sphinx = new Sphinx($this->_db);
    $d = $sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }

    $html = file_get_html($url);
    $dataToSave = array();

    if ($html && $html->find('h1', 0)) {
      $prix = $image = $title = $urlAnnonces = '';
      $title = $html->find('h1', 0)->plaintext;
      $title = strip_tags($title);
      $title = trim($title);
      $date = '';
      $date = $html->find('.annonce .date span', 0)->plaintext;
      $date = str_replace('Publié le: ', '', $date);
      $date = str_replace('/', '-', $date);
      $date = strtotime($date);

      if ($html->find('.annonce .price span', 0)) {
        $prix = trim($html->find('.annonce .price span', 0)->plaintext);
        $prix = str_replace('Dhs', '', $prix);
        $prix = trim($prix);
      }
      $imageUnique='';
      if ($html->find('.annonce .adphoto img', 0)) {
        $image = trim($html->find('.annonce .adphoto img', 0)->src);
        $imageUnique = Utilities::resizeandsave($image, $data['idSites']);
      }
      if ($html->find('.annonce .date span', 1)) {
        $ville = $html->find('.annonce .date span', 1)->plaintext;
        $ville = trim($ville);
        $ville = explode(' ', $ville);
        $ville = trim($ville[0]);
        $idVille = Utilities::getVille($ville);
      }
      if ($html->find('.breadcrumb li', 2)) {
        $tags = Utilities::getTags(array($html->find('.breadcrumb li', 2)->plaintext));
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
        'idSphinx' => $data['prefix'] . $annonceID,
        'idAnnonce' => $annonceID,
        'idSite' => $data['idSites'],
        'title' => trim($title),
        'description' => trim($description),
        'date' => $date,
        'ville' => array($idVille => $ville),
        'tags' => $tags,
        'image' => $imageUnique,
        'prix' => $prix,
        'url' => $url,
        'extraKeywords' => $extraKeywords
      );
    }
    return $dataToSave;
  }

  public function fetchALLAnnonces($nbr) {
    $sitesTable = new \Zend\Db\TableGateway\TableGateway('sites', $this->_db);
    $sphinx = new Sphinx($this->_db);
    $rowset = $sitesTable->select(array('idSites' => 6));
    $data = $rowset->current();

    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = file_get_html('http://www.souk.ma/fr/Maroc/&q=&p=' . $i);
      foreach ($listpagehtml->find('.results .item') as $item) {
        $link = $item->find('a', 0)->href;
        $dataToSave = $this->getData($link, $data);
        if (!empty($dataToSave)) {
         $sphinx->SaveToSphinx($dataToSave);
        } 
      }
    }
  }

}
