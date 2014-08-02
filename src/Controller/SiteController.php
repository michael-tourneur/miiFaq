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
 * @Route("/faq", name="@miiFaq")
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

        $limit = 10; //$this->extension->getConfig('miiFaq.question_per_page', 10);
        $count = $query->count();
        
        if ($this['request']->isXmlHttpRequest()) {
            $list = [];
            foreach ($query->get() as $key => $question) {
                $list[] = ['title' => $question->getTitle(), 'id' => $question->getId(), 'url' => $this['url']->to('@miiFaq/question/show', ['id' => $question->getId()])];
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
            'questionEntity' => new Question,
            'filter' => $filter,
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

        $viewed = $this['session']->get('miiFaq.questions.viewed', []);
        if(!in_array($id, $viewed)) {
            $question->setViewCount( $question->getViewCount() + 1 );
            $this->questions->save($question);
            $viewed[] = $id;
            $this['session']->set('miiFaq.questions.viewed', $viewed);
        }

        $query = $this->answers->query()->where(['status = ?'], [Answer::STATUS_APPROVED]);

        $this['db.em']->related($question, 'comments', $query);

        return [
            'head.title' => __($question->getTitle()), 
            'question' => $question,
            'answers' => $question->getComments(),
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
