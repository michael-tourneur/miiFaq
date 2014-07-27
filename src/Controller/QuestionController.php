<?php

namespace Mii\Faq\Controller;

use Mii\Faq\Entity\Question;
use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Framework\Controller\Exception;
use Pagekit\User\Entity\UserRepository;

/**
 * @Access("faq: manage content", admin=true)
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
        $this->roles = $this['users']->getRoleRepository();
        $this->users 			= $this['users']->getUserRepository();
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
            $data['date'] = $this['dates']->getDateTime($date)->setTimezone(new \DateTimeZone('UTC'));

            $this->questions->save($question, $data);

            return ['message' => $id ? __('Question saved.') : __('Question created.'), 'id' => $question->getId()];

        } catch (Exception $e) {

            return ['message' => $e->getMessage(), 'error' => true];

        }
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