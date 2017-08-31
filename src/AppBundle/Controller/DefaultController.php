<?php

namespace AppBundle\Controller;

use AppBundle\Form\SearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;
use FS\SolrBundle\Doctrine\Hydration\HydrationModes;

class DefaultController extends Controller {
  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request) {
    $city = 'tous';
    $searchForm = $this->createForm(SearchForm::class);

    if ($request->isMethod('POST')) {
      $searchForm->handleRequest($request);
      $data = $searchForm->getData();
      $params = array(
        'ville' => !is_null($data['villes']) ? str_replace(' ', '-', $data['villes']->getSlug())
          : 'tous',
        'tags' => !is_null($data['tags']) ? str_replace(' ', '-', $data['tags']->getSlug())
          : 'tous',
        'keys' => str_replace(' ', '-', $data['text']),
      );
      return $this->redirect($this->generateUrl('list_annonces', $params));
    }

    $randomAnnonces = $this->get('appbundle.annonces_manager')->getRandomAnnonces();

    return $this->render(
      'AppBundle:Default:index.html.twig', array(
        'form' => $searchForm->createView(),
        'city' => $city,
        'randomAnnonces' => $randomAnnonces,
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

    $tagss = $em->getRepository(
      'AppBundle:Tags'
    )->findOneBy(array('slug' => $tags));
    if ($tagss) {
      $tagss = $em->getReference("AppBundle:Tags", $tagss->getIdTags());
      $form->get('tags')->setData($tagss);
    }

    return $this->render(
      'AppBundle:Default:search.html.twig', array(
        'form' => $form->createView(),
      )
    );
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function footerAction(Request $request) {
    $em = $this->getDoctrine()->getManager();
    $tags = $em->getRepository(
      'AppBundle:Tags'
    )->findAll();

    return $this->render(
      'AppBundle:Default:footer.html.twig', array(
        'tags' => $tags,
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
    $annoncesSimilar = $em->getRepository('AppBundle:Annonces')->findAllSimilar(
      $ville, $annonce->getTags()
    );
    return $this->render(
      'AppBundle:Default:detail.html.twig', array(
        'annonce' => $annonce,
        'annoncesSimilar' => $annoncesSimilar,
      )
    );
  }

  /**
   * @Route(
   *     "/annonces/{ville}/{tags}/{keys}",
   *     name="list_annonces",
   * )
   */
  public function listAction(Request $request, $ville = 'tous', $tags = 'tous', $keys = '') {
    $page = $request->get('page', 1);
    $annonces = $this->get('appbundle.annonces_manager')->findAllPaginates($ville, $tags, $keys);
    $nbArticlesParPage = 25;
    $pagination = array(
      'page' => $page,
      'nbPages' => ceil(count($annonces) / $nbArticlesParPage),
      'nomRoute' => 'list_annonces',
      'paramsRoute' => array('ville' => $ville, 'tags' => $tags, 'keys' => $keys),
    );

    $queryparams = $request->query->all();

    $listTags = [];
    return $this->render(
      'AppBundle:Default:list.html.twig', array(
        'annonces' => $annonces,
        'pagination' => $pagination,
        'arguments' => array(
          'ville' => str_replace('-', ' ', $ville),
          'tags' => str_replace('-', ' ', $tags),
          'keys' => str_replace('-', ' ', $keys),
        ),
        'queryparams' => $queryparams,
        'countAnnonces' => count($annonces),
        'listTags' => $listTags,
      )
    );
  }

}


/*'chercher/:sourceId(/)(:term)(/:ville)(/:tags)(/:order)(/:page)': 'searchImages',
  'chercher-annonce/:sourceId(/)(/:tags)(/:idannonce--:title)': 'showSingle'*/