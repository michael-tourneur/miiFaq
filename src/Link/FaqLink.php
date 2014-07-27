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
        return $route == '@miiFaq/site' || $route == '@miiFaq/id';
    }

    /**
     * @{inheritdoc}
     */
    public function renderForm($link, $params = [], $context = '')
    {
        $questions = [];
        return $this['view']->render('extension://miiFaq/views/admin/link/faq.razr', compact('link', 'params', 'questions'));
    }
}
