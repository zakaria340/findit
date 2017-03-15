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
   *     "/chercher/{ville}/{idannonce}/{title}",
   *     name="detail_annonces",
   *     requirements={
   *         "idannonce": "\d*"
   *     }
   * )
   */
  public function detailAction($ville ='', $idannonce = '', $title = '') {
    var_dump(
      $title . '---------$title'
    );
    die;
  }

  /**
   * @Route(
   *     "/chercher/{ville}/{tags}/{keys}",
   *     name="list_annonces",
   *     defaults={"page" = 1},
   * )
   */
  public function listAction($ville = '', $tags = '', $keys = '') {
    var_dump(
      $ville . '-------$ville', $tags . '------$idannonce', $keys . '---------$keys'
    );


    /*   $conn = $this->getSphinxQLConx();
   $query = SphinxQL::create($conn)->select('*')->from('annonces10');

   $query->limit(100000000);
   $resultcount = $query->execute();
   */


    /*  $em = $this->getDoctrine()->getManager();
      $annonces = $em->getRepository(
        'AppBundle:Annonces'
      )//->join('e.idRelatedEntity', 'r')
      ->findAll();*/

    return $this->render(
      'AppBundle:Default:list.html.twig', array(
        'annonces' => 'Annonce',
        '',
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

}


/*'chercher/:sourceId(/)(:term)(/:ville)(/:tags)(/:order)(/:page)': 'searchImages',
  'chercher-annonce/:sourceId(/)(/:tags)(/:idannonce--:title)': 'showSingle'*/