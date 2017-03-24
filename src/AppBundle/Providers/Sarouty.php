<?php

namespace AppBundle\Providers;

use Sunra\PhpSimple\HtmlDomParser;

Class Sarouty {

  public $_baseUrl = '';
  protected $em;
  protected $sphinx;

  public function __construct($em, $sphinx) {
    $this->em = $em;
    $this->sphinx = $sphinx;
  }

  public function getData($url, $data) {
    $urldata = explode('-', $url);
    $urldata = end($urldata);
    $s = preg_match_all('/(.*).html/', $urldata, $matches);
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
    $date = '';
    if ($html && $html->find('h1', 0)) {
      $prix = $image = $title = $urlAnnonces = '';

      if ($html->find('#primary-content .swipe-wrap', 0)) {
        $image = trim($html->find('#primary-content .swipe-wrap img', 0)->src);
        $image = str_replace('&amp;', '&', $image);
        $image = 'http:' . $image;
      }
      if ($image != '') {
        $imageUnique = md5(time() . 3 . $annonceID) . '.jpg';
        $pathNewImage = $this->sphinx->resizeandsave($image, $data->getIdSites(), $imageUnique);
      }
      else {
        return array();
      }

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
      $ville = $html->find('#breadcrumbs .breadcrumb-item ', 0);
      $ville = trim($ville->find('a', 0)->plaintext);
      $idVille = $this->sphinx->getVille($ville);
      $cat1 = $html->find('#property-facts .fixed-table tr', 1);
      $cat1 = $cat1->find('td', 0)->plaintext;
      $tags = $this->sphinx->getTags(array($cat1));
      $extraKeywords = array();

      foreach ($html->find('#property-facts .fixed-table tr') as $liinfo) {
        $value = str_replace('MAD', '', trim($liinfo->find('td', 0)->plaintext));
        $dataitem = array(
          'label' => $liinfo->find('th', 0)->plaintext,
          'value' => trim($value),
        );
        array_push($extraKeywords, $dataitem);
      }


      if ($html->find('meta[property=og:description]', 0)) {
        $description = trim($html->find('meta[property=og:description]', 0)->content);
      }
      $dataToSave = array(
        'idSphinx'      => $data->getPrefix() . $annonceID,
        'idAnnonce'     => $annonceID,
        'idSite'        => $data->getIdSites(),
        'title'         => trim($title),
        'description'   => trim($description),
        'date'          => ($date =='') ? time() : $date,
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

  /**
   * @param $nbr
   */
  public function fetchALLAnnonces($nbr = 2) {
    $site = $this->em->getRepository('AppBundle:Sites')->find(4);
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = HtmlDomParser::file_get_html('https://www.sarouty.ma/recherche?l=&page=' . $i);
      foreach ($listpagehtml->find('#primary-content .serp-result h2') as $item) {
        $link = $item->find('a', 0);
        if ($link) {
          $link = 'https://www.sarouty.ma' . $link->href;
          $dataToSave = $this->getData($link, $site);
          if (!empty($dataToSave)) {
            $this->sphinx->SaveToSphinx($dataToSave);
          }
        }
      }
    }
  }

}
