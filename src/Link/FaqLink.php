<?php

namespace Mii\Faq\Link;

use Pagekit\System\Link\Link;

class FaqLink extends Link
{
    /**
     * @{inheritdoc}
     */
    public function getId()
    {
        return 'miiFaq';
    }

    /**
     * @{inheritdoc}
     */
    public function getLabel()
    {
        return __('miiFaq');
    }

    /**
     * @{inheritdoc}
     */
    public function accept($route)
    {
        return $route == '@miiFaq/site' || $route == '@miiFaq/site/question/id';
    }

    /**
     * @{inheritdoc}
     */
    public function renderForm($link, $params = [], $context = '')
    {
        $questions = $this['db.em']->getRepository('Mii\Faq\Entity\Question')->findAll();
        return $this['view']->render('extension://miiFaq/views/admin/link/miiFaq.razr', compact('link', 'params', 'questions'));
    }
}
