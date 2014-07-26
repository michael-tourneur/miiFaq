<?php

namespace Mii\Faq\Entity;

use Pagekit\Page\Entity\Page;
use Pagekit\System\Entity\DataTrait;
use Pagekit\User\Entity\AccessTrait;

/**
 * @Entity(tableClass="@faq_questions", eventPrefix="page.page")
 */
class Question
{
    use AccessTrait, DataTrait;

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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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
}
