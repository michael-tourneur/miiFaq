<?php

namespace Mii\Faq\Entity;

use Pagekit\Page\Entity\Page;
use Pagekit\System\Entity\DataTrait;
use Pagekit\User\Entity\AccessTrait;
use Pagekit\Comment\CommentsTrait;
use Pagekit\Framework\Database\Event\EntityEvent;

/**
 * @Entity(tableClass="@miifaq_questions")
 */
class Question
{
    use AccessTrait, DataTrait, CommentsTrait;

    /* question closed status. */
    const STATUS_CLOSED = 0;

    /* question open status. */
    const STATUS_OPEN = 1;

    /* question answered status. */
    const STATUS_ANSWERED = 2;

    /* question resolved status. */
    const STATUS_RESOLVED = 3;

    /** @Column(type="integer") @Id */
    protected $id;

    /** @Column(type="integer") */
    protected $user_id;

    /** @Column(type="string") */
    protected $slug;

    /**
     * @HasMany(targetEntity="Answer", keyFrom="id", keyTo="question_id")
     * @OrderBy({"date" = "DESC"})
     */
    protected $comments;

    /**
     * @BelongsTo(targetEntity="Pagekit\User\Entity\User", keyFrom="user_id")
     */
    protected $user;

    /** @Column(type="string") */
    protected $title;

    /** @Column(type="integer") */
    protected $status = self::STATUS_OPEN;

    /** @Column */
    protected $content = '';

    /** @Column(type="datetime")*/
    protected $date;

    /** @Column(type="datetime") */
    protected $modified;

    /** @Column(type="integer") */
    protected $comment_count = 0;

    /** @Column(type="integer") */
    protected $view_count = 0;

    /** @Column(type="integer") */
    protected $vote = 0;

     /** @Column(type="integer") */
    protected $best_answer = 0;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getModified()
    {
        return $this->modified;
    }

    public function setModified(\DateTime $modified)
    {
        $this->modified = $modified;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatusText()
    {
        $statuses = self::getStatuses();

        return isset($statuses[$this->status]) ? $statuses[$this->status] : __('Unknown');
    }

    public static function getStatuses()
    {   
        return [
            self::STATUS_OPEN           => __('Open'),
            self::STATUS_ANSWERED       => __('Answered'),
            self::STATUS_RESOLVED       => __('Resolved'),
            self::STATUS_CLOSED         => __('Closed')
        ];
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getViewCount()
    {
        return $this->view_count;
    }

    public function setViewCount($viewCount)
    {
        $this->view_count = $viewCount;
    }

    public function getVote()
    {
        return $this->vote;
    }

    public function getCommentCount()
    {
        return $this->comment_count;
    }

    public function commentCountPlus()
    {
        return $this->comment_count += 1;
    }

    public function getBestAnswer()
    {
        return $this->best_answer;
    }

    public function setBestAnswer($answer)
    {
        $this->best_answer = (int) $answer;
    }

    /**
     * @PreSave
     */
    public function preSave(EntityEvent $event)
    {
        $this->modified = new \DateTime;

        $repository = $event->getEntityManager()->getRepository(get_class($this));

        $i = 2;
        $id = $this->id;

        while ($repository->query()->where('slug = ?', [$this->slug])->where(function($query) use($id) { if ($id) $query->where('id <> ?', [$id]); })->first()) {
            $this->slug = preg_replace('/-\d+$/', '', $this->slug).'-'.$i++;
        }
    }

    /**
     * @PreDelete
     */
    public function preDelete(EntityEvent $event)
    {
        $event->getConnection()->delete('@miifaq_answers', ['question_id' => $this->getId()]);
    }
}
