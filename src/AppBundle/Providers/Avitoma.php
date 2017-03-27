<?php

namespace AppBundle\Providers;

Class Avitoma {

  public $_baseUrl = 'http://www.avito.ma/vi/';
  protected $em;
  protected $sphinx;

  public function __construct($em, $sphinx) {
    $this->em = $em;
    $this->sphinx = $sphinx;
  }

  public function getData($annonce, $category, $data) {
    $url = $annonce->url;
    $url = str_replace('vij', 'vi', $url);
    /**
     * Check if Annonce exist.
     */
    $d = $this->sphinx->checkAnnoncebyUrl($url);
    if (!empty($d)) {
      return array();
    }

    if (isset($annonce->full_ad_data->image) && $annonce->full_ad_data->image->standard != '') {
      $imageUnique = md5(time() . 3 . $annonce->id) . '.jpg';
      $pathNewImage = $this->sphinx->resizeandsave($annonce->full_ad_data->image->standard, $data->getIdSites(), $imageUnique);
    }
    else {
      return array();
    }

    $idVille = $this->sphinx->getVille($annonce->full_ad_data->region);
    $tags = $this->sphinx->getTags(array($category));
    $price = '';
    if (isset($annonce->full_ad_data->price->value) && !is_null($annonce->full_ad_data->price->value)) {
      $price = $annonce->full_ad_data->price->value;
    }
    $description = '';
    if (isset($annonce->full_ad_data->body) && !is_null($annonce->full_ad_data->body)) {
      $description = strip_tags($annonce->full_ad_data->body);
    }

    $extraKeywords = array();

    foreach ($annonce->full_ad_data->ad_details as $detail) {
      $detail = (array) $detail;
      if (!empty($detail)) {
        array_push($extraKeywords, array('label' => $detail['label'], 'value' => $detail['value']));
      }
    }
    $date = 0;
    if (isset($annonce->full_ad_data->date)) {
      $date = strtotime($annonce->full_ad_data->date);
    }
    $dataToSave = array(
      'idSphinx'      => $data->getPrefix() . $annonce->id,
      'idAnnonce'     => $annonce->id,
      'idSite'        => $data->getIdSites(),
      'title'         => $annonce->subject,
      'description'   => $description,
      'date'          => $date,
      'ville'         => array($idVille => $annonce->full_ad_data->region),
      'tags'          => $tags,
      'image'         => $pathNewImage,
      'prix'          => $price,
      'url'           => $url,
      'extraKeywords' => $extraKeywords,
    );

    return $dataToSave;
  }

  /**
   * @param $url
   *
   * @return mixed
   */
  public function getjsonFromUrl($url) {
    $json = file_get_contents($url);
    $obj = json_decode($json);
    return $obj;
  }

  /**
   * @param $nbr
   */
  public function fetchALLAnnonces() {
    $site = $this->em->getRepository('AppBundle:Sites')->find(1);
    $villes = $this->getjsonFromUrl('http://www.avito.ma/templates/api/confregions.js?v=3');
    $categories = array(
      array('id' => 5010, 'name' => 'Téléphones'),
      array('id' => 5080, 'name' => 'Tablettes'),

      array('id' => 5030, 'name' => 'Ordinateurs portables'),
      array('id' => 2010, 'name' => 'Voitures'),
      array('id' => 2030, 'name' => 'Motos'),
      );
    foreach ($villes->regions as $ville) {
      foreach ($categories as $category) {
        $url_annonces = 'http://www.avito.ma/lij?fullad=1&q=&w=112&ca=' . $ville->id . '_s&cg=' . $category['id']
          . '&st=s';
        $annonces = $this->getjsonFromUrl($url_annonces);
        foreach ($annonces->list_ads as $annonce) {
          $dataToSave = $this->getData($annonce, $category['name'], $site);
          if (!empty($dataToSave)) {
            $this->sphinx->SaveToSphinx($dataToSave);
          }
        }
      }
    }

  }

}
