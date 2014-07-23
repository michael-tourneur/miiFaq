<?php

namespace Mii\Faq\Controller;

use Pagekit\Framework\Controller\Controller;

/**
 * @Route("/faq")
 */
class SiteController extends Controller
{

		/**
     * @Response("extension://miiFaq/views/index.razr")
     */
    public function indexAction()
    {
        return ['head.title' => __('FAQ')];
    }
}
