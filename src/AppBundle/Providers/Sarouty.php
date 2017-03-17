<?php

Class Sarouty {

  public $_baseUrl = '';
  public $_db;

  public function __construct($db) {
    $this->_db = $db;
  }

  public function getData($url, $data) {
    $html = file_get_html($url);
    $dataToSave = array();
    $date = '';
    if ($html && $html->find('h1', 0)) {
      $urldata = explode('-', $url);
      $urldata = end($urldata);
      $s = preg_match_all('/(.*).html/', $urldata, $matches);
      $annonceID = $matches[1][0];

      /**
       * Check if Annonce exist.
       */
      $sphinx = new Sphinx($this->_db);
      $d = $sphinx->checkAnnoncebyUrl($url);
      if (!empty($d)) {
        return array();
      }

      $prix = $image = $title = $urlAnnonces = '';
      $title = trim($html->find('h1', 0)->plaintext);
      if ($html->find('#property-amenities .last-date', 0)) {
        $date = $html->find('#property-amenities .last-date', 0)->plaintext;
        $date = str_replace('Dernière mise à jour:', '', $date);
        $date = trim($date);
        $timestamp = strtotime(str_replace('/', '-', $date));
        $date = $timestamp;
      }

      if ($html->find('#property-facts .price', 0)) {
        $prix = trim($html->find('#property-facts .price .val', 0)->plaintext);

        $prix = trim($prix);
      }
      if ($html->find('#primary-content .swipe-wrap', 0)) {
        $image = trim($html->find('#primary-content .swipe-wrap img', 0)->src);
        $image = str_replace('&amp;', '&', $image);
        $image = 'http:' . $image;
      }

      $ville = $html->find('#breadcrumbs .breadcrumb-item ', 0);
      $ville = trim($ville->find('a', 0)->plaintext);
      $idVille = Utilities::getVille($ville);
      $cat1 = $html->find('#property-facts .fixed-table tr', 1);
      $cat1 = $cat1->find('td', 0)->plaintext;
      $tags = Utilities::getTags(array($cat1));
      $extraKeywords = array();

      foreach ($html->find('#property-facts .fixed-table tr') as $liinfo) {
        $value = str_replace('MAD', '', trim($liinfo->find('td', 0)->plaintext));
        $dataitem = array('label' => $liinfo->find('th', 0)->plaintext,
          'value' => trim($value));
        array_push($extraKeywords, $dataitem);
      }
      if ($image != '') {
        $imageUnique = md5(time() . 3 . $annonceID) . '.jpg';
        Utilities::resizeandsave($image, $data['idSites'], $imageUnique);
      } else {
        $imageUnique = '';
      }

      if ($html->find('meta[property=og:description]', 0)) {
        $description = trim($html->find('meta[property=og:description]', 0)->content);
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
    $sphinx = new Sphinx($this->_db);
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = file_get_html('https://www.sarouty.ma/recherche?l=&page=' . $i);
      foreach ($listpagehtml->find('#primary-content .serp-result h2') as $item) {
        $sitesTable = new \Zend\Db\TableGateway\TableGateway('sites', $this->_db);
        $rowset = $sitesTable->select(array('idSites' => 4));
        $dataSite = $rowset->current();
        $link = $item->find('a', 0);
        $link = 'https://www.sarouty.ma' . $link->href;
        $dataToSave = $this->getData($link, $dataSite);
        if (!empty($dataToSave)) {
          $sphinx->SaveToSphinx($dataToSave);
        }
      }
    }
  }

}
