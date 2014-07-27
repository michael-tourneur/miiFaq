<?php

namespace Mii\Faq\Controller;

use Mii\Faq\Entity\Question;
use Mii\Faq\Entity\Answer;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Framework\Controller\Exception;
use Pagekit\User\Entity\UserRepository;


/**
 * @Route("/faq", name="@miiFaq")
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
        $this->answers  = $this['db.em']->getRepository('Mii\Faq\Entity\Answer');
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

    /**
     * @Route("/question/add", name="@miiFaq/question/add")
     * @Response("extension://miiFaq/views/question/edit.razr")
     */
    public function addQuestionAction()
    {
        $question = new Question;
        $question->setUser($this['user']);

        return [
            'head.title' => __('Add Question'), 
            'question' => $question, 
            'statuses' => Question::getStatuses(), 
        ];
    }

    /**
     * @Route("/{id}", name="@miiFaq/question/show")
     * @Response("extension://miiFaq/views/question/view.razr")
     */
    public function showQuestionAction($id)
    {
        if (!$question = $this->questions->where(['id = ?', 'date < ?'], [$id, new \DateTime])->first()) {
            return $this['response']->create(__('Post not found!'), 404);
        }

        $query = $this->answers->query()->where(['status = ?'], [Answer::STATUS_APPROVED]);

        $this['db.em']->related($question, 'answers', $query);

        return [
            'head.title' => __($question->getTitle()), 
            'question' => $question
        ];
    }



    /**
     * @Route("/question/save", name="@miiFaq/question/save")
     * @Request({"id": "int", "question": "array"})
     * @Response("json")
     */
    public function saveAction($id = null, $data)
    {
        $questionController = new QuestionController();
        return $questionController->saveAction($id, $data);
    }

}
