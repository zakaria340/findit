<?php
namespace AppBundle\Providers;

use Sunra\PhpSimple\HtmlDomParser;

Class Marocannonces {

  public $_baseUrl = 'https://www.marocannonces.com/categorie/397/Location-vacances/annonce/';
  protected $em;
  protected $sphinx;

  public function __construct($em, $sphinx) {
    $this->em = $em;
    $this->sphinx = $sphinx;
  }

  public function getData($url, $data) {
    $s = preg_match_all('/annonce\/(.*)\//', $url, $matches);
    if (empty($matches[0])) {
      return;
    }
    if (!empty($matches[0])) {
      $annonceID = $matches[1][0];
    }
    /**
     * Check if Annonce exist.
     */
    $d = $this->sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }

    $header = get_headers($url, 1);
    if ($header[0] == 'HTTP/1.1 200 OK') {
      $html = HtmlDomParser::file_get_html($url);
    }
    else {
      $html = FALSE;
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

      if ($image && $image != '') {
        $imageUnique = md5(time() . $data->getIdSites() . $annonceID) . '.jpg';
        $pathNewImage = $this->sphinx->resizeandsave($image, $data->getIdSites(), $imageUnique);
      }
      else {
        return array();
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
      $idVille = $this->sphinx->getVille($ville);
      $tags = $this->sphinx->getTags(array($categorie1));

      $extraKeywords = array();
      foreach ($html->find('#extra_questions li') as $li) {
        $tagg = explode(':', $li->plaintext);
        array_push($extraKeywords, array('label' => trim($tagg[0]), 'value' => trim($tagg[1])));
      }

      $dataToSave = array(
        'idSphinx'      => $data->getPrefix() . $annonceID,
        'idAnnonce'     => $annonceID,
        'idSite'        => $data->getIdSites(),
        'title'         => trim($title),
        'description'   => trim($description),
        'date'          => $date,
        'ville'         => array($idVille => $ville),
        'tags'          => $tags,
        'image'         => $pathNewImage,
        'prix'          => (string) $prix,
        'url'           => $url,
        'extraKeywords' => $extraKeywords,
      );
    }
    return $dataToSave;
  }

  public function fetchALLAnnonces($nbr = 2) {
    $site = $this->em->getRepository('AppBundle:Sites')->find(2);
    for ($i = 1; $i <= $nbr; $i++) {
      $listpagehtml = HtmlDomParser::file_get_html('https://www.marocannonces.com/maroc.html?image=on&pge=' . $i);

      foreach ($listpagehtml->find('#content .cars-list li') as $item) {

        if ($item->find('a', 0)) {
          $link = 'https://www.marocannonces.com/' . $item->find('a', 0)->href;
          $dataToSave = $this->getData($link, $site);
          if (!empty($dataToSave)) {
            $this->sphinx->SaveToSphinx($dataToSave);
          }
        }
      }
      die;
    }
  }

}
