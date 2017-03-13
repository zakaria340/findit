<?php

namespace AppBundle\Controller;

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

 /*   $conn = $this->getSphinxQLConx();
    $query = SphinxQL::create($conn)->select('*')->from('annonces10');

    $query->limit(100000000);
    $resultcount = $query->execute();
    */



    $em = $this->getDoctrine()->getManager();
    $annonces = $em->getRepository('AppBundle:Annonces')
      //->join('e.idRelatedEntity', 'r')
      ->findAll();

    var_dump($annonces);
    die;
    // replace this example code with whatever you need
    return $this->render(
      'default/index.html.twig', [
      'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
    ]
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