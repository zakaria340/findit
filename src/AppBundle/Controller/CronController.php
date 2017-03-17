<?php

namespace AppBundle\Controller;

use AppBundle\Form\SearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;

class CronController extends Controller {
  /**
   * @Route("/cron", name="cronpage")
   */
  public function indexAction(Request $request) {
    $crawl = $this->container->get('appbundle.crawl');
    $crawl->CrawlOn();
  }

}