<?php

Class Marocannonces {

  public $_baseUrl = 'http://www.marocannonces.com/categorie/397/Location-vacances/annonce/';
  public $_db;

  public function __construct($db) {
    $this->_db = $db;
  }

  public function getData($url, $data) {
    $s = preg_match_all('/annonce\/(.*)\//', $url, $matches);
    if (empty($matches[0])) {
      return;
    }
    if (!empty($matches[0])) {
      $annonceID = $matches[1][0];
    }
    $header = get_headers($url, 1);
    if ($header[0] == 'HTTP/1.1 200 OK') {
      $html = file_get_html($url);
    } else {
      $html = false;
    }

    /**
     * Check if Annonce exist.
     */
    $sphinx = new Sphinx($this->_db);
    $d = $sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }


    $dataToSave = array();
    if ($html && $html->find('.description h1', 0)) {
      $prix = $image = $title = $description = $categorie1 = '';
      $title = $html->find('.description h1', 0)->plaintext;
      $date = trim($html->find('.description ul.info-holder li', 1)->plaintext);
      if ($html->find('.description .price span', 0)) {
        $prix = trim($html->find('.description .price span', 0)->plaintext);
      }

      if ($html->find('meta[property=og:image]', 0)) {
        $image = trim($html->find('meta[property=og:image]', 0)->content);
        $image = str_replace('cdn.', '', $image);
      }
      foreach ($html->find('#bloc-advsearch-top select[name=cat] option') as $checkbox) {
        if ($checkbox->selected) {
          $categorie1 = $checkbox->plaintext;
        }
      }
      if ($html->find('.description .parameter .block', 0)) {
        $description = $html->find('.description .parameter .block', 0)->plaintext;
        $description = strip_tags($description);
        $description = str_replace("Detail de l'annonce :", '', $description);
      }

      $ville = trim($html->find('.description ul.info-holder li', 0)->plaintext);
      $idVille = Utilities::getVille($ville);
      $tags = Utilities::getTags(array($categorie1));

      if ($image && $image != '') {
        $imageUnique = Utilities::resizeandsave($image, $data['idSites']);
      } else {
        $imageUnique = '';
      }

      $extraKeywords = array();
      foreach ($html->find('#extra_questions li') as $li) {
        $tagg = explode(':', $li->plaintext);
        array_push($extraKeywords, array('label' => trim($tagg[0]), 'value' => trim($tagg[1])));
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
        'prix' => (string) $prix,
        'url' => $url,
        'extraKeywords' => $extraKeywords
      );
    }
    return $dataToSave;
  }

  public function fetchALLAnnonces($nbr) {
    $sitesTable = new \Zend\Db\TableGateway\TableGateway('sites', $this->_db);
    $sphinx = new Sphinx($this->_db);
    $rowset = $sitesTable->select(array('idSites' => 2));
    $data = $rowset->current();
    for ($i = 2; $i <= 3; $i++) {
      $listpagehtml = file_get_html('http://www.marocannonces.com/maroc.html?image=on&pge=' . $i);
      foreach ($listpagehtml->find('#content .cars-list li') as $item) {
        $link = 'http://www.marocannonces.com/' . $item->find('a', 0)->href;
        $dataToSave = $this->getData($link, $data);
        if (!empty($dataToSave)) {
          $sphinx->SaveToSphinx($dataToSave);
        }
      }
    }
  }

}
