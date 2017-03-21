<?php

namespace AppBundle\Providers;

use Sunra\PhpSimple\HtmlDomParser;

Class Wandaloo {

  public $_baseUrl = '';
  protected $em;
  protected $sphinx;

  public function __construct($em, $sphinx) {
    $this->em = $em;
    $this->sphinx = $sphinx;
  }

  public function getData($url, $data) {
    /**
     * Check if Annonce exist.
     */
    $d = $this->sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }

    $html = HtmlDomParser::file_get_html($url);
    $dataToSave = array();

    if ($html && $html->find('h1.paveOccasion', 0)) {
      $urldata = explode('/', $url);
      $urldata = end($urldata);
      $s = preg_match_all('/(.*).html/', $urldata, $matches);
      $annonceID = $matches[1][0];
      $prix = $image = $title = $urlAnnonces = '';

      if ($html->find('meta[property=og:image]', 0)) {
        $image = trim($html->find('meta[property=og:image]', 0)->content);
      }
      if ($image != '') {
        $path_info = pathinfo($image);
        if ($path_info['extension'] != 'png' && $path_info['extension'] != 'PNG') {
          $imageUnique = md5(time() . $data->getIdSites() . $annonceID) . '.jpg';
          $pathNewImage = $this->sphinx->resizeandsave($image, $data->getIdSites(), $imageUnique);
        } else {
          return array();
        }
      }
      else {
        return array();
      }

      $title = $html->find('h1.paveOccasion', 0)->plaintext;
      $date = $html->find('#sommaire ul', 0)->lastChild()->plaintext;
      $date = str_replace('Date de publication', '', $date);
      $date = trim($date);
      //$date = $html->find('#sommaire ul li span.rouge',0)->plaintext;
      $date = strtotime($date);
      if ($html->find('#occasionHeader h4', 0)) {
        $prix = trim($html->find('#occasionHeader h4', 0)->plaintext);
        $prix = str_replace('DH', '', $prix);
        $prix = trim($prix);
      }
      $idVille = 0;
      $ville = '';
      if ($html->find('#vendeur li#Nom ', 0)) {
        $ville = trim($html->find('#vendeur li#Nom ', 0)->innertext);

        $ville = explode('<br/>', $ville);
        $ville = $ville[1];

        $ville = explode(',', $ville);
        $ville = trim($ville[0]);

        $idVille = $this->sphinx->getVille($ville);

      }
      $cat1 = trim('Voitures');
      $tags = $this->sphinx->getTags(array($cat1));
      $extraKeywords = array();

      foreach ($html->find('#sommaire li') as $liinfo) {
        $dataitem = array(
          'label' => $liinfo->find('p', 0)->plaintext,
          'value' => trim($liinfo->find('span', 0)->plaintext),
        );
        array_push($extraKeywords, $dataitem);
      }

      $typeCarburant = $html->find('#sommaire li', 2);
      array_push($extraKeywords, $typeCarburant->find('span', 0)->plaintext);
      $datemise = $html->find('#sommaire li', 3);
      array_push($extraKeywords, $datemise->find('span', 0)->plaintext);
      $boitevitesse = $html->find('#sommaire li', 6);
      array_push($extraKeywords, $boitevitesse->find('span', 0)->plaintext);

      $dataToSave = array(
        'idSphinx'      => $data->getPrefix() . $annonceID,
        'idAnnonce'     => $annonceID,
        'idSite'        => $data->getIdSites(),
        'title'         => trim($title),
        'description'   => trim($html->find('#oComment p', 0)->plaintext),
        'date'          => $date,
        'ville'         => array($idVille => $ville),
        'tags'          => $tags,
        'image'         => $pathNewImage,
        'prix'          => $prix,
        'url'           => $url,
        'extraKeywords' => $extraKeywords,
      );
    }
    return $dataToSave;
  }

  public function fetchALLAnnonces($nbr = 2) {
    $site = $this->em->getRepository('AppBundle:Sites')->find(3);
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = HtmlDomParser::file_get_html('http://www.wandaloo.com/occasion/?pg=' . $i);

      foreach ($listpagehtml->find('#left #resultat .sortable tbody .evenrow') as $item) {
        $link = $item->getAttribute('onclick');
        $link = str_replace("GoToLink('", '', $link);
        $link = str_replace("');", '', $link);
        $dataToSave = $this->getData($link, $site);
        if (!empty($dataToSave)) {
          $this->sphinx->SaveToSphinx($dataToSave);
        }
      }
    }
  }

}
