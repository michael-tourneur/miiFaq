<?php

namespace Mii\Faq\Controller;

use Mii\Faq\Entity\Question;
use Mii\Faq\Entity\Answer;
use Pagekit\Component\Database\ORM\Repository;
use Pagekit\Framework\Controller\Controller;
use Pagekit\Framework\Controller\Exception;
use Pagekit\User\Entity\UserRepository;

/**
 * @Route("/admin/miifaq", name="@miiFaq/")
 * @Access("miiFaq: manage content", admin=true)
 */
class AnswerController extends Controller
{
	const POSTS_PER_PAGE = 20;

    /**
     * @var Repository
     */
    protected $questions;

    /**
     * @var Repository
     */
    protected $answers;

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
        $this->questions    = $this['db.em']->getRepository('Mii\Faq\Entity\Question');
        $this->answers 	    = $this['db.em']->getRepository('Mii\Faq\Entity\Answer');
        $this->roles        = $this['users']->getRoleRepository();
        $this->users 		= $this['users']->getUserRepository();
    }

    /**
     * @Request({"id": "int", "answer": "array"}, csrf=true)
     * @Response("json")
     */
    public function saveAction($id, $data)
    {
        if(!$data['content']) return ['message' => __('Answer\'s message is required.'), 'error' => true];
        try {

            if (!$question = $this->questions->find($data['question_id'])) {

                return ['message' => __('Question associated not found.'), 'error' => true];

            }

            if (!$answer = $this->answers->find($id)) {

                $answer = new Answer;
                $answer->setUserId((int) $this['user']->getId());
                $questionData['comment_count'] = $question->commentCountPlus();

            }

            if($question->getStatus() == Question::STATUS_OPEN)
                $questionData['status'] = Question::STATUS_ANSWERED;

            $date = (isset($data['date'])) ? $data['date'] : time();
            $data['modified'] = $this['dates']->getDateTime($date)->setTimezone(new \DateTimeZone('UTC'));

            $data['date'] = $id ? $question->getDate() : $data['modified'];

            $this->answers->save($answer, $data); 

            if($questionData)
                $this->questions->save($question, $questionData);       

            return ['message' => $id ? __('Answer saved.') : __('Answer created.'), 'id' => $answer->getId()];

        } catch (Exception $e) {

            return ['message' => $e->getMessage(), 'error' => true];

        }
    }


     /**
     * @Request({"id": "int", "vote": "boolean"})
     * @Response("json")
     */
    public function voteAnswerAction($id, $vote) 
    {
        try {

            if (!$answer = $this->answers->find($id)) {
                return ['message' => __('Answer not found.'), 'error' => true];
            }

            if($vote)
                $answer->setVotePlus();
            else
                $answer->setVoteMinus();

            $this->answers->save($answer); 

            return ['message' => __('Vote added.'), 'vote' => $answer->getVote(), 'error' => false];

        } catch (Exception $e) {

            return ['message' => $e->getMessage(), 'error' => true];

        }
    }

    /**
     * @Request({"id": "int"})
     * @Response("json")
     */
    public function bestAnswerAction($id)
    {
        try {

            if (!$answer = $this->answers->find($id)) {
                return ['message' => __('Answer not found.'), 'error' => true];
            }

            if (!$question = $this->questions->find($answer->getQuestionId())) {
                return ['message' => __('Question associated not found.'), 'error' => true];
            }

            $answer->setVoteBest();
            $this->answers->save($answer); 

            $question->setBestAnswer($id);
            $this->questions->save($question); 

            return ['message' => __('Vote added.'), 'vote' => $answer->getVote(), 'error' => false];

        } catch (Exception $e) {

            return ['message' => $e->getMessage(), 'error' => true];

        }
    }
}