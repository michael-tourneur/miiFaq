<?php

namespace Mii\Faq\Controller;

use Mii\Faq\Entity\Question;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Component\Database\ORM\Repository;


/**
 * @Route("/faq")
 */
class SiteController extends Controller
{

    /**
     * @var Repository
     */
    protected $questions;

    /**
     * @var Repository
     */
    protected $answers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->questions     = $this['db.em']->getRepository('Mii\Faq\Entity\Question');
        // $this->answers  = $this['db.em']->getRepository('Mii\Faq\Entity\Answers');
    }

		/**
     * @Response("extension://miiFaq/views/index.razr")
     */
    public function indexAction()
    {	
    		$questions = $this->questions->query()->where(['status = ?', 'date < ?'], [Question::STATUS_OPEN, new \DateTime])->orderBy('date', 'DESC')->get();

        return [
            'head.title' => __('FAQ'),
            'questions' => $questions,
        ];
    }
}
