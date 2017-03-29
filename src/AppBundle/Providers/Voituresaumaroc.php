<?php

namespace AppBundle\Providers;

use Sunra\PhpSimple\HtmlDomParser;

Class Voituresaumaroc {

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

      if ($html->find('#bigpic #gallery img', 0)) {
        $image = trim($html->find('#bigpic #gallery img', 0)->src);
      }
      if ($image != '') {
        $imageUnique = md5(time() . 7 . $annonceID) . '.jpg';
        $pathNewImage = $this->sphinx->resizeandsave($image, $data->getIdSites(), $imageUnique);
      }
      else {
        return array();
      }

      $title = trim($html->find('h1', 0)->plaintext);

      if ($html->find('.adv_info_dealer_extra .adv_c', 0)) {
        $date = $html->find('.adv_info_dealer_extra .adv_c strong', 0);
        if($date) {
          $date = trim($date->plaintext);
          if ($date == "Aujourd'hui") {
            $date = time();
          }
          else {
            $timestamp = strtotime($date);
            $date = $timestamp;
          }
        }else {
          $date = time();
        }
      }

      if ($html->find('.adv_price_title h2', 0)) {
        $prix = trim($html->find('.adv_price_title h2', 0)->plaintext);
        $prix = trim($prix);
      }

      $ville = $html->find('.adv_info_dealer_extra .adv_l', 0);
      $ville = trim($ville->plaintext);
      $idVille = $this->sphinx->getVille($ville);
      $tags = $this->sphinx->getTags(array('Voitures'));
      $extraKeywords = array();

      foreach ($html->find('.product-fields-others .product-fields') as $liinfo) {

        $keywords = trim(strip_tags($liinfo->plaintext));
        $keywords = explode(':', $keywords);
        $dataitem = array(
          'label' => $keywords[0],
          'value' => trim(strip_tags($keywords[1])),
        );
        array_push($extraKeywords, $dataitem);
      }

      if ($html->find('.adv_desc_title  pre', 0)) {
        $description = trim($html->find('.adv_desc_title  pre', 0)->plaintext);
      }
      $dataToSave = array(
        'idSphinx'      => $data->getPrefix() . $annonceID,
        'idAnnonce'     => $annonceID,
        'idSite'        => $data->getIdSites(),
        'title'         => trim($title),
        'description'   => trim($description),
        'date'          => ($date == '') ? time() : $date,
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
    $site = $this->em->getRepository('AppBundle:Sites')->find(7);
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = HtmlDomParser::file_get_html(
        'http://voiture24.ma/voiture-occasion/?p=' . $i . '&tpcid=1458899314&cat_id=&vm=l&sv=std&smd=simple'
      );
      foreach ($listpagehtml->find('#plist-recherche-list .item') as $item) {
        $link = $item->find('button', 0);
        if (isset($link->attr['onclick'])) {

          $linkUrl = str_replace("location.href='", '', $link->attr['onclick']);
          $linkUrl = str_replace("'", '', $linkUrl);
          $dataToSave = $this->getData($linkUrl, $site);
          if (!empty($dataToSave)) {
            $this->sphinx->SaveToSphinx($dataToSave);
          }
        }
      }
    }
  }

}
