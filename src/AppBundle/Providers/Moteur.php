<?php

namespace AppBundle\Providers;

use Sunra\PhpSimple\HtmlDomParser;

Class Moteur {

  public $_baseUrl = '';
  protected $em;
  protected $sphinx;

  public function __construct($em, $sphinx) {
    $this->em = $em;
    $this->sphinx = $sphinx; 
  }

  public function getData($url, $data) {
    $s = preg_match_all('/detail-annonce\/(.*)\//', $url, $matches);
    $annonceID = $matches[1][0];

    $d = $this->sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }
    
    $html = HtmlDomParser::file_get_html($url);
    $dataToSave = array();

    if ($html && $html->find('h1 .text_bold', 0)) {
      $prix = $image = $title = $urlAnnonces = '';
      if ($html->find('#gallery-wrapper a img', 0)) {
        $image = trim($html->find('#gallery-wrapper a img', 0)->src);
      }
      if ($image != '') {
        $imageUnique = md5(time() . 3 . $annonceID) . '.jpg';
        $pathNewImage = $this->sphinx->resizeandsave($image, $data->getIdSites(), $imageUnique);
      } else {
        return array();
      }
      $title = $html->find('h1 .text_bold', 0)->plaintext;
      $title = strip_tags($title);
      $title = trim($title);
      $date = '';
      if ($html->find('.car-detail .col-md-8 .row .detail_line', 4)) {
        $date = $html->find('.car-detail .col-md-8 .row .detail_line', 4)->find('span', 1)->plaintext;
        $date = trim($date);
        $date = strtotime($date);
      }
      if ($html->find('h1 .price-block', 0)) {
        $prix = trim($html->find('h1 .price-block', 0)->plaintext);
        $prix = str_replace('Dhs', '', $prix);
        $prix = trim($prix);
      }

      if ($html->find('#gallery-wrapper a img', 0)) {
        $image = trim($html->find('#gallery-wrapper a img', 0)->src);
      }

      $ville = $html->find('.block_tele li ', 4);
      $ville = $ville->find('a', 0)->plaintext;
      $ville = trim($ville);
      $idVille = $this->sphinx->getVille($ville);

      $tags =  $this->sphinx->getTags(array('Voitures'));

      $extraKeywords = array();

      if ($html->find('.breadcrumb li', 2)) {
        $daextra = array(
          'label' => 'Marque',
          'value' => trim($html->find('.breadcrumb li', 2)->plaintext),
        );
        array_push($extraKeywords, $daextra);
      }

      if ($html->find('.breadcrumb li', 3)) {
        $daextra = array(
          'label' => 'Modèle',
          'value' => trim($html->find('.breadcrumb li', 3)->plaintext),
        );
        array_push($extraKeywords, $daextra);
      }

      foreach ($html->find('.car-detail .col-md-8 .row .detail_line') as $liinfo) {
        if ($liinfo->find('span', 1)) {
          $dataitem = array('label' => trim($liinfo->find('span', 0)->plaintext),
            'value' => trim($liinfo->find('span', 1)->plaintext));
          array_push($extraKeywords, $dataitem);
        }
      }
      $description = '';
      if ($html->find('meta[NAME=description]', 0)) {
        $description = $html->find('meta[NAME=description]', 0)->attr['content'];
      }

      array_pop($extraKeywords);
      array_pop($extraKeywords);
  

      $dataToSave = array(
        'idSphinx' => $data->getPrefix() . $annonceID,
        'idAnnonce' => $annonceID,
        'idSite' => $data->getIdSites(),
        'title' => trim(strip_tags($title)),
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

  /**
   * @param int $nbr
   */
  public function fetchALLAnnonces($nbr = 2) {
    $site = $this->em->getRepository('AppBundle:Sites')->find(5);

    for ($i = 1; $i <= $nbr; $i++) {
      $str = 'http://www.moteur.ma/fr/voiture/achat-voiture-occasion/' . $i;
      $listpagehtml = HtmlDomParser::file_get_html($str);

      foreach ($listpagehtml->find('#bloc-resultat-recherche-auto .row-item') as $item) {
        $link = $item->find('h2 a', 0);
        if($link) {
          $href = $link->href;
          $dataToSave = $this->getData($href, $site);
          if (!empty($dataToSave)) {
            $this->sphinx->SaveToSphinx($dataToSave);
          }
        }
        die('A');
      }
    }
  }


  /**
   * @param $annonce
   */
  public function upextratags($annonce) {
    $url = $annonce->getUrl();
    $html = HtmlDomParser::file_get_html($url);
    $extraKeywords = array();

    if ($html->find('.breadcrumb li', 2)) {
      $daextra = array(
        'label' => 'Marque',
        'value' => trim($html->find('.breadcrumb li', 2)->plaintext),
      );
      array_push($extraKeywords, $daextra);
    }

    if ($html->find('.breadcrumb li', 3)) {
      $daextra = array(
        'label' => 'Modèle',
        'value' => trim($html->find('.breadcrumb li', 3)->plaintext),
      );
      array_push($extraKeywords, $daextra);
    }

    foreach ($html->find('.car-detail .col-md-8 .row .detail_line') as $liinfo) {
      if ($liinfo->find('span', 1)) {
        $dataitem = array('label' => trim($liinfo->find('span', 0)->plaintext),
                          'value' => trim($liinfo->find('span', 1)->plaintext));
        array_push($extraKeywords, $dataitem);
      }
    }
    $extraKeywords = json_encode($extraKeywords);
    $annonce->setExtraKeywords($extraKeywords);
    $this->em->persist($annonce);
    $this->em->flush();
  }

}
