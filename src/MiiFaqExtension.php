<?php

namespace Mii\Faq;

use Pagekit\Framework\Application;
use Pagekit\Extension\Extension;
use Pagekit\System\Event\LinkEvent;

class MiiFaqExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        parent::boot($app);

        // $app['events']->addSubscriber(new HelloListener());
        // 
        $app->on('system.link', function(LinkEvent $event) {
            $event->register('Mii\Faq\Link\FaqLink');
        });

        $app->on('system.init', function() use ($app) {

            $this->config += $app['option']->get("{$this->name}:config", []);

            $app['config']->set('app.frontpage', $app['config']->get('app.frontpage') ?: '@faq/site');

        }, 15);
    }
}
