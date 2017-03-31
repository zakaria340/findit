<?php

namespace AppBundle\Controller;

use AppBundle\Form\SearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;
use Symfony\Component\HttpFoundation\Response;

class AmpController extends Controller {
  /**
   * @Route("/amp", name="homepageamp")
   */
  public function indexampAction(Request $request) {
    return $this->redirect($this->generateUrl('homepage'));
  }

  /**
   * @Route(
   *     "/amp/annonce/{ville}/{idannonce}/{title}",
   *     name="detail_annoncesamp",
   *     requirements={
   *         "idannonce": "\d*"
   *     }
   * )
   */
  public function detailAction($ville = '', $idannonce = '', $title = '') {
    $params = array(
      'ville' => $ville,
      'idannonce'  => $idannonce,
      'title'  => $title,
    );
    return $this->redirect($this->generateUrl('detail_annonces', $params));
  }

  /**
   * @Route(
   *     "/amp/annonces/{ville}/{tags}/{keys}",
   *     name="list_annoncesamp",
   * )
   */
  public function listAction(Request $request, $ville = 'tous', $tags = 'tous', $keys = '') {

    $params = array(
      'ville' => $ville,
      'tags'  => $tags,
      'keys'  => $keys,
    );
    return $this->redirect($this->generateUrl('list_annonces', $params));
  }
}