<?php

namespace AppBundle\Controller;

use AppBundle\Form\SearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;

class DefaultController extends Controller {
  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request) {

    $ip = "92.3.2.10";//$_SERVER['REMOTE_ADDR'] $this->get_real_ip();
    $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));

    $city = 'casablanca';
    if ($query && $query['status'] == 'success') {
      //$city = $this->clean($query['city']);
    }
    $searchForm = $this->createForm(SearchForm::class);

    if ($request->isMethod('POST')) {
      $this->forward('AppBundle:Default:list', $_POST);
      $searchForm->handleRequest($request);
      $data = $searchForm->getData();
      $params = array(
        'ville' => str_replace(' ', '-', $data['villes']->getSlug()),
        'tags'  => str_replace(' ', '-', $data['tags']->getSlug()),
        'keys'  => str_replace(' ', '-', $data['text']),
      );
      return $this->redirect($this->generateUrl('list_annonces', $params));
    }

    return $this->render(
      'AppBundle:Default:index.html.twig', array(
        'form' => $searchForm->createView(),
        'city' => $city,
      )
    );

  }


  public function searchAction(Request $request, $ville = '', $tags = '', $keys = '') {
    $form = $this->createForm(SearchForm::class);

    /**
     *
     */
    $keys = str_replace('-', ' ', $keys);
    $form->get('text')->setData($keys);
    $em = $this->getDoctrine()->getManager();

    $villes = $em->getRepository(
      'AppBundle:Villes'
    )->findOneBy(array('slug' => $ville));
    if ($villes) {
      $villes = $em->getReference("AppBundle:Villes", $villes->getIdVilles());
      $form->get('villes')->setData($villes);
    }


    $tags = $em->getRepository(
      'AppBundle:Tags'
    )->findOneBy(array('slug' => $ville));
    if ($tags) {
      $tags = $em->getReference("AppBundle:Tags", $tags->getIdVilles());
      $form->get('tags')->setData($tags);
    }

    return $this->render(
      'AppBundle:Default:search.html.twig', array(
        'form' => $form->createView(),
      )
    );
  }

  /**
   * @Route(
   *     "/annonce/{ville}/{idannonce}/{title}",
   *     name="detail_annonces",
   *     requirements={
   *         "idannonce": "\d*"
   *     }
   * )
   */
  public function detailAction($ville = '', $idannonce = '', $title = '') {
    $em = $this->getDoctrine()->getManager();
    $annonce = $em->getRepository('AppBundle:Annonces')->find($idannonce);
    if (!$annonce) {
      return $this->redirect($this->generateUrl('homepage'));
    }

    return $this->render(
      'AppBundle:Default:detail.html.twig', array(
        'annonce' => $annonce,
      )
    );
  }

  /**
   * @Route(
   *     "/annonces/{ville}/{tags}/{keys}",
   *     name="list_annonces",
   * )
   */
  public function listAction(Request $request, $ville = '', $tags = '', $keys = '') {
    $page = $request->get('page', 1);
    $conn = $this->getSphinxQLConx();
    $query = SphinxQL::create($conn)->select('*')->from('annonces11');

    if (!is_null($keys) && $keys != '') {
      $query->match(array('title', 'description', 'tags', 'ville'), $keys);
    }
    if (!is_null($ville)) {
      $query->match(array('ville'), $ville);
    }
    if (!is_null($tags)) {
      //$query->match(array('tags'), $tags);
    }

    $query->limit(100000000);
    $result = $query->execute();
    $ids_count = array();
    foreach ($result as $item) {
      $ids_count[] = $item['id'];
    }
    $nbArticlesParPage = 10;
    $em = $this->getDoctrine()->getManager();
    $annonces = $em->getRepository('AppBundle:Annonces')->findAllPagineEtTrie($page, $nbArticlesParPage, $ids_count);

    $pagination = array(
      'page'        => $page,
      'nbPages'     => ceil(count($annonces) / $nbArticlesParPage),
      'nomRoute'    => 'list_annonces',
      'paramsRoute' => array('ville' => $ville, 'tags' => $tags, 'keys' => $keys),
    );

/*    foreach($annonces as $annonce) {
      var_dump($annonce->getImage());
    }*/

    return $this->render(
      'AppBundle:Default:list.html.twig', array(
        'annonces'   => $annonces,
        'pagination' => $pagination,
        'arguments'  => array('ville' => $ville, 'tags' => $tags, 'keys' => $keys),
      )
    );
  }


  /**
   * @return \Foolz\SphinxQL\Connection
   */
  public function getSphinxQLConx() {
    $conn = new Connection();
    $conn->setParams(array('host' => '127.0.0.1', 'port' => 9306));
    return $conn;
  }

  public function clean($string) {
    $a = array(
      'À',
      'Á',
      'Â',
      'Ã',
      'Ä',
      'Å',
      'Æ',
      'Ç',
      'È',
      'É',
      'Ê',
      'Ë',
      'Ì',
      'Í',
      'Î',
      'Ï',
      'Ð',
      'Ñ',
      'Ò',
      'Ó',
      'Ô',
      'Õ',
      'Ö',
      'Ø',
      'Ù',
      'Ú',
      'Û',
      'Ü',
      'Ý',
      'ß',
      'à',
      'á',
      'â',
      'ã',
      'ä',
      'å',
      'æ',
      'ç',
      'è',
      'é',
      'ê',
      'ë',
      'ì',
      'í',
      'î',
      'ï',
      'ñ',
      'ò',
      'ó',
      'ô',
      'õ',
      'ö',
      'ø',
      'ù',
      'ú',
      'û',
      'ü',
      'ý',
      'ÿ',
      'Ā',
      'ā',
      'Ă',
      'ă',
      'Ą',
      'ą',
      'Ć',
      'ć',
      'Ĉ',
      'ĉ',
      'Ċ',
      'ċ',
      'Č',
      'č',
      'Ď',
      'ď',
      'Đ',
      'đ',
      'Ē',
      'ē',
      'Ĕ',
      'ĕ',
      'Ė',
      'ė',
      'Ę',
      'ę',
      'Ě',
      'ě',
      'Ĝ',
      'ĝ',
      'Ğ',
      'ğ',
      'Ġ',
      'ġ',
      'Ģ',
      'ģ',
      'Ĥ',
      'ĥ',
      'Ħ',
      'ħ',
      'Ĩ',
      'ĩ',
      'Ī',
      'ī',
      'Ĭ',
      'ĭ',
      'Į',
      'į',
      'İ',
      'ı',
      'Ĳ',
      'ĳ',
      'Ĵ',
      'ĵ',
      'Ķ',
      'ķ',
      'Ĺ',
      'ĺ',
      'Ļ',
      'ļ',
      'Ľ',
      'ľ',
      'Ŀ',
      'ŀ',
      'Ł',
      'ł',
      'Ń',
      'ń',
      'Ņ',
      'ņ',
      'Ň',
      'ň',
      'ŉ',
      'Ō',
      'ō',
      'Ŏ',
      'ŏ',
      'Ő',
      'ő',
      'Œ',
      'œ',
      'Ŕ',
      'ŕ',
      'Ŗ',
      'ŗ',
      'Ř',
      'ř',
      'Ś',
      'ś',
      'Ŝ',
      'ŝ',
      'Ş',
      'ş',
      'Š',
      'š',
      'Ţ',
      'ţ',
      'Ť',
      'ť',
      'Ŧ',
      'ŧ',
      'Ũ',
      'ũ',
      'Ū',
      'ū',
      'Ŭ',
      'ŭ',
      'Ů',
      'ů',
      'Ű',
      'ű',
      'Ų',
      'ų',
      'Ŵ',
      'ŵ',
      'Ŷ',
      'ŷ',
      'Ÿ',
      'Ź',
      'ź',
      'Ż',
      'ż',
      'Ž',
      'ž',
      'ſ',
      'ƒ',
      'Ơ',
      'ơ',
      'Ư',
      'ư',
      'Ǎ',
      'ǎ',
      'Ǐ',
      'ǐ',
      'Ǒ',
      'ǒ',
      'Ǔ',
      'ǔ',
      'Ǖ',
      'ǖ',
      'Ǘ',
      'ǘ',
      'Ǚ',
      'ǚ',
      'Ǜ',
      'ǜ',
      'Ǻ',
      'ǻ',
      'Ǽ',
      'ǽ',
      'Ǿ',
      'ǿ',
    );
    $b = array(
      'A',
      'A',
      'A',
      'A',
      'A',
      'A',
      'AE',
      'C',
      'E',
      'E',
      'E',
      'E',
      'I',
      'I',
      'I',
      'I',
      'D',
      'N',
      'O',
      'O',
      'O',
      'O',
      'O',
      'O',
      'U',
      'U',
      'U',
      'U',
      'Y',
      's',
      'a',
      'a',
      'a',
      'a',
      'a',
      'a',
      'ae',
      'c',
      'e',
      'e',
      'e',
      'e',
      'i',
      'i',
      'i',
      'i',
      'n',
      'o',
      'o',
      'o',
      'o',
      'o',
      'o',
      'u',
      'u',
      'u',
      'u',
      'y',
      'y',
      'A',
      'a',
      'A',
      'a',
      'A',
      'a',
      'C',
      'c',
      'C',
      'c',
      'C',
      'c',
      'C',
      'c',
      'D',
      'd',
      'D',
      'd',
      'E',
      'e',
      'E',
      'e',
      'E',
      'e',
      'E',
      'e',
      'E',
      'e',
      'G',
      'g',
      'G',
      'g',
      'G',
      'g',
      'G',
      'g',
      'H',
      'h',
      'H',
      'h',
      'I',
      'i',
      'I',
      'i',
      'I',
      'i',
      'I',
      'i',
      'I',
      'i',
      'IJ',
      'ij',
      'J',
      'j',
      'K',
      'k',
      'L',
      'l',
      'L',
      'l',
      'L',
      'l',
      'L',
      'l',
      'l',
      'l',
      'N',
      'n',
      'N',
      'n',
      'N',
      'n',
      'n',
      'O',
      'o',
      'O',
      'o',
      'O',
      'o',
      'OE',
      'oe',
      'R',
      'r',
      'R',
      'r',
      'R',
      'r',
      'S',
      's',
      'S',
      's',
      'S',
      's',
      'S',
      's',
      'T',
      't',
      'T',
      't',
      'T',
      't',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'W',
      'w',
      'Y',
      'y',
      'Y',
      'Z',
      'z',
      'Z',
      'z',
      'Z',
      'z',
      's',
      'f',
      'O',
      'o',
      'U',
      'u',
      'A',
      'a',
      'I',
      'i',
      'O',
      'o',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'U',
      'u',
      'A',
      'a',
      'AE',
      'ae',
      'O',
      'o',
    );
    $string = str_replace($a, $b, $string);
    $string = strtolower($string);
    $string = addslashes($string);
    $string = str_replace(' ', '-', $string);
    return $string;
  }

  public function get_real_ip() {

    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
      return $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
      return $_SERVER["HTTP_X_FORWARDED"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
      return $_SERVER["HTTP_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED"])) {
      return $_SERVER["HTTP_FORWARDED"];
    }
    else {
      return $_SERVER["REMOTE_ADDR"];
    }
  }

}


/*'chercher/:sourceId(/)(:term)(/:ville)(/:tags)(/:order)(/:page)': 'searchImages',
  'chercher-annonce/:sourceId(/)(/:tags)(/:idannonce--:title)': 'showSingle'*/