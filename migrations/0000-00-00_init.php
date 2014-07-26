<?php

return [

    'up' => function() use ($app) {

        $util = $app['db']->getUtility();

        if ($util->tableExists('@faq_questions') === false) {
            $util->createTable('@faq_questions', function($table) {
                $table->addColumn('id', 'integer', ['unsigned' => true, 'length' => 10, 'autoincrement' => true]);
                $table->addColumn('user_id', 'integer', ['unsigned' => true, 'length' => 10, 'default' => 0]);
                $table->addColumn('slug', 'string', ['length' => 255]);
                $table->addColumn('title', 'string', ['length' => 255]);
                $table->addColumn('status', 'smallint');
                $table->addColumn('content', 'text');
                $table->addColumn('date', 'datetime', ['notnull' => false]);
                $table->addColumn('modified', 'datetime');
                $table->addColumn('comment_count', 'integer', ['default' => 0]);
                $table->setPrimaryKey(['id']);
                $table->addUniqueIndex(['slug'], 'POSTS_SLUG');
                $table->addIndex(['title'], 'TITLE');
                $table->addIndex(['user_id'], 'USER_ID');
            });
        }

        // if ($util->tableExists('@blog_comment') === false) {
        //     $util->createTable('@blog_comment', function($table) {
        //         $table->addColumn('id', 'integer', ['unsigned' => true, 'length' => 10, 'autoincrement' => true]);
        //         $table->addColumn('parent_id', 'integer', ['unsigned' => true, 'length' => 10]);
        //         $table->addColumn('post_id', 'integer', ['unsigned' => true, 'length' => 10]);
        //         $table->addColumn('user_id', 'string', ['length' => 255]);
        //         $table->addColumn('author', 'string', ['length' => 255]);
        //         $table->addColumn('email', 'string', ['length' => 255]);
        //         $table->addColumn('url', 'string', ['length' => 255, 'notnull' => false]);
        //         $table->addColumn('ip', 'string', ['length' => 255]);
        //         $table->addColumn('created', 'datetime');
        //         $table->addColumn('content', 'text');
        //         $table->addColumn('status', 'smallint');
        //         $table->setPrimaryKey(['id']);
        //         $table->addIndex(['author'], 'AUTHOR');
        //         $table->addIndex(['created'], 'CREATED');
        //         $table->addIndex(['status'], 'STATUS');
        //         $table->addIndex(['post_id'], 'POST_ID');
        //         $table->addIndex(['post_id', 'status'], 'POST_ID_STATUS');
        //     });
        // }
    }

];
