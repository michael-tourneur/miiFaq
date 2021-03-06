<?php

namespace Mii\Faq\Controller;

use Mii\Faq\MiiFaqExtension;
use Mii\Faq\Entity\Question;
use Mii\Faq\Entity\Answer;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Framework\Controller\Exception;
use Pagekit\User\Entity\UserRepository;


/**
 * @Route("miiFaq", name="@miiFaq/site")
 */
class SiteController extends Controller
{
    /**
     * @var MiiFaqExtension
     */
    protected $extension;

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
    public function __construct(MiiFaqExtension $extension)
    {
        $this->questions    = $extension;
        $this->questions    = $this['db.em']->getRepository('Mii\Faq\Entity\Question');
        $this->answers      = $this['db.em']->getRepository('Mii\Faq\Entity\Answer');
    }

	/**
     * @Request({"filter": "array", "page":"int"})
     * @Response("extension://miiFaq/views/index.razr")
     */
    public function indexAction($filter = null, $page = 0)
    {	  
    	$query = $this->questions->query()->where(['date' => new \DateTime]);

        if ($filter) {
            $this['session']->set('miiFaq.index.filter', $filter);
        } else {
            $filter = $this['session']->get('miiFaq.index.filter', []);
        }

        $query = $this->questions->query();

        if (isset($filter['status']) && is_numeric($filter['status'])) {
            $query->where(['status' => intval($filter['status'])]);
        }

        if (isset($filter['orderby']) && in_array($filter['orderby'], ['vote', 'answer_count', 'view_count'])) {
            $query->orderBy($filter['orderby'], 'ASC');
        }

        if (isset($filter['search']) && strlen($filter['search'])) {
            $query->where(function($query) use ($filter) {
                $query->orWhere(['title LIKE ?'], ["%{$filter['search']}%"]);
            });
        }

        $limit = 20; //$this->extension->getConfig('index.question_per_page', 20);
        $count = $query->count();
            
        if ($this['request']->isXmlHttpRequest()) {
            $list = [];
            foreach ($query->get() as $key => $question) {
                $list[] = ['title' => $question->getTitle(), 'id' => $question->getId(), 'url' => $this['url']->to('@miiFaq/site/question/id', ['id' => $question->getId()])];
            }
            return $this['response']->json([
                'list' => $list,
                'count' => $count
            ]);
        }

        $total = ceil($count / $limit);
        $page  = max(0, min($total - 1, $page));
        $query->offset($page * $limit)->limit($limit)->get();

        return [
            'head.title' => __('FAQ'),
            'questions' => $query->get(),
            'filter' => $filter,
            'question' => new Question
        ];
    }

    /**
     * @Route("/question/add", name="@miiFaq/site/question/add")
     * @Response("extension://miiFaq/views/question/edit.razr")
     */
    public function addQuestionAction()
    {        
        $question = new Question;
        $question->setUserId((int) $this['user']->getId());
        return [
            'head.title' => __('Add Question'), 
            'question' => $question, 
            'statuses' => Question::getStatuses(), 
        ];
    }

    /**
     * @Route("/question/save", name="@miiFaq/site/question/save")
     * @Request({"id": "int", "question": "array"})
     * @Response("json")
     */
    public function saveQuestionAction($id = null, $data)
    {
        $questionController = new QuestionController();
        $response = $questionController->saveAction($id, $data);
        if($this['request']->isXmlHttpRequest())
            return $response;

        return $this->redirect($this['url']->route('@miiFaq/site/question/id', ['id' => $response['id']]));
    }

    /**
     * @Route("/question/{id}", name="@miiFaq/site/question/id")
     * @Request({"id": "int", "filter": "array"})
     * @Response("extension://miiFaq/views/question/view.razr")
     */
    public function showQuestionAction($id, $filter = null)
    {

        if (!$question = $this->questions->where(['id = ?', 'date < ?'], [$id, new \DateTime])->first()) {
            return $this['response']->create(__('Question not found!'), 404);
        }

        $viewed = $this['session']->get('miiFaq.questions.viewed', []);
        if(!in_array($id, $viewed)) {
            $question->setViewCount( $question->getViewCount() + 1 );
            $this->questions->save($question);
            $viewed[] = $id;
            $this['session']->set('miiFaq.questions.viewed', $viewed);
        }

        if ($filter) {
            $this['session']->set('miiFaq.question.answers.filter', $filter);
        } else {
            $filter = $this['session']->get('miiFaq.question.answers.filter', []);
        }

        $query = $this->answers->query();

        if (!isset($filter['order'] ) || ( isset($filter['order'] ) && !in_array($filter['order'], ['asc', 'desc']))) {
            $filter['order'] = 'desc';
        }

        if (isset($filter['orderby']) && in_array($filter['orderby'], ['vote', 'date'])) {
            $query->orderBy($filter['orderby'], $filter['order']);
        }

        $query->where(['status = ?'], [Answer::STATUS_APPROVED]);

        $this['db.em']->related($question, 'comments', $query);

        if($this['request']->isXmlHttpRequest())
            return $this['response']->json([
                'table' => $this['view']->render('extension://miiFaq/views/answer/table.razr', ['answers' => $question->getComments()]), 
                'count' => count($question->getComments()), 
            ]);

        return [
            'head.title' => __($question->getTitle()), 
            'question' => $question,
            'answers' => $question->getComments(),
            'filter' => $filter,
        ];
    }

    /**
     * @Route("/answer/save", name="@miiFaq/site/answer/save")
     * @Request({"id": "int", "answer": "array"})
     * @Response("json")
     */
    public function saveAnswerAction($id = null, $data)
    {
        $answerController = new AnswerController();
        $response = $answerController->saveAction($id, $data);
        if($this['request']->isXmlHttpRequest())
            return $response;

        return $this->redirect($this['url']->route('@miiFaq/site/question/id', ['id' => $data['question_id']]));
    }

    /**
     * @Route("/question/{question}/answer/{id}/vote", name="@miiFaq/site/answer/id/vote")
     * @Request({"id": "int", "question": "int", "vote": "boolean"})
     * @Response("json")
     */
    public function voteAnswerAction($id, $question, $vote)
    {

        $voted = $this['session']->get('miiFaq.question.answers.voted', []);
        if(isset($voted[$id]) && $voted[$id] == $vote)
            $response = ['message' => __('Already voted.'), 'error' => true];
        else {
            $answerController = new AnswerController();
            $response = $answerController->voteAnswerAction($id, $vote);
        }
        $voted[$id] = $vote;
        $this['session']->set('miiFaq.question.answers.voted', $voted);

        if($this['request']->isXmlHttpRequest())
            return $response;

        return $this->redirect($this['url']->route('@miiFaq/site/question/id', ['id' => $question]));
    }


    /**
     * @Route("/question/{question}/answer/{id}/best", name="@miiFaq/site/answer/id/best")
     * @Request({"id": "int", "question": "int"})
     * @Response("json")
     */
    public function bestAnswerAction($id, $question)
    {   

        $answerController = new AnswerController();
        $response = $answerController->bestAnswerAction($id);

        if($this['request']->isXmlHttpRequest())
            return $response;

        return $this->redirect($this['url']->route('@miiFaq/site/question/id', ['id' => $question]));
    }

}
