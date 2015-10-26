<?php


namespace Empathy\ELib\Blog;

use Empathy\ELib\Model;


class Service
{

    public static function processTags($b, $tags_arr, $cats_arr=array())
    {
        // deal with tags
        $bt = Model::load('BlogTag');
        $bt->removeAll($b->id);

        $t = Model::load('TagItem');

        if (strlen($_POST['tags']) > 0) {
            $tag_ids = $t->getIds($tags_arr, false);

            foreach ($tag_ids as $id) {
                $bt = Model::load('BlogTag');
                $bt->blog_id = $b->id;
                $bt->tag_id = $id;
                $bt->insert(Model::getTable('BlogTag'), 0, array(), 0);
            }
        }
        $t->cleanup();        
    }

}