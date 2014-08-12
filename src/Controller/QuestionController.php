<?php

namespace Mii\Faq\Controller;

use Mii\Faq\Entity\Question;
use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Framework\Controller\Exception;
use Pagekit\User\Entity\UserRepository;

/**
 * @Route(name="@miiFaq/admin/question")
 * @Access("miiFaq: manage content", admin=true)
 */
class QuestionController extends Controller
{
	const POSTS_PER_PAGE = 20;

    /**
     * @var Repository
     */
    protected $questions;

    /**
     * @var Repository
     */
    protected $roles;

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->questions 	= $this['db.em']->getRepository('Mii\Faq\Entity\Question');
        $this->roles        = $this['users']->getRoleRepository();
        $this->users 		= $this['users']->getUserRepository();
    }

    /**
     * @Request({"filter": "array", "page":"int"})
     * @Response("extension://miiFaq/views/admin/question/index.razr")
     */
    public function indexAction($filter = null, $page = 0)
    {
        $query = $this->questions->query();


        $limit = 10; //$this->extension->getConfig('posts.posts_per_page');
        $count = $query->count();
        $total = ceil($count / $limit);
        $page  = max(0, min($total - 1, $page));
        $questions = $query->offset($page * $limit)->limit($limit)->related('user')->orderBy('date', 'DESC')->get();

        if ($this['request']->isXmlHttpRequest()) {
            return $this['response']->json([
                'table' => $this['view']->render('extension://miiFaq/views/admin/question/table.razr', ['count' => $count, 'questions' => $questions]),
                'total' => $total
            ]);
        }

        return [
            'head.title' => __('Questions'), 
            'questions' => $questions, 
            'statuses' => Question::getStatuses(), 
            'filter' => $filter, 
            'total' => $total, 
            'count' => $count, 
            'pending' => $pending
        ];
    }

    /**
     * @Response("extension://miiFaq/views/question/edit.razr")
     */
    public function addAction()
    {
        $question = new Question;
        $question->setUser($this['user']);
        // $question->setCommentStatus(true);

        return [
        	'head.title' => __('Add Question'), 
        	'question' => $question, 
        	'statuses' => Question::getStatuses(), 
        	'roles' => $this->roles->findAll(), 
        	'users' => $this->users->findAll()
        ];
    }

    /**
     * @Request({"id": "int", "question": "array"}, csrf=true)
     * @Response("json")
     */
    public function saveAction($id, $data)
    {
        try {

            if (!$question = $this->questions->find($id)) {

                $question = new Question;
                $question->setUser($this['user']);

            }
            // var_dump($question); die();
            $slug = (isset($data['slug'])) ? $data['slug'] : $data['title'];
            if (!$data['slug'] = $this->slugify($slug)) {
                throw new Exception('Invalid slug.');
            }

            $date = (isset($data['date'])) ? $data['date'] : time();
            $data['modified'] = $this['dates']->getDateTime($date)->setTimezone(new \DateTimeZone('UTC'));

            $data['date'] = $id ? $question->getDate() : $data['modified'];

            $this->questions->save($question, $data);

            return ['message' => $id ? __('Question saved.') : __('Question created.'), 'id' => $question->getId()];

        } catch (Exception $e) {

            return ['message' => $e->getMessage(), 'error' => true];

        }
    }

    /**
     * @Request({"id": "int"})
     * @Response("extension://miiFaq/views/admin/question/edit.razr")
     */
    public function editAction($id)
    {
        try {

            if (!$question = $this->questions->query()->where(compact('id'))->related('user')->first()) {
                throw new Exception(__('Invalid post id.'));
            }

        } catch (Exception $e) {

            $this['message']->error($e->getMessage());

            return $this->redirect('@miiFaq/admin/question');
        }

        return [
            'head.title' => __('Edit Question'), 
            'question' => $question, 
            'statuses' => Question::getStatuses(), 
            'roles' => $this->roles->findAll(), 
            'users' => $this->users->findAll()
        ];
    }




    protected function slugify($slug)
    {
        $slug = preg_replace('/\xE3\x80\x80/', ' ', $slug);
        $slug = str_replace('-', ' ', $slug);
        $slug = preg_replace('#[:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]#', "\x20", $slug);
        $slug = str_replace('?', '', $slug);
        $slug = trim(mb_strtolower($slug, 'UTF-8'));
        $slug = preg_replace('#\x20+#', '-', $slug);

        return $slug;
    }

}