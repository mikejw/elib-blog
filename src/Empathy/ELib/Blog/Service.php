<?php

namespace Empathy\ELib\Blog;

use Elasticsearch\ClientBuilder;
use Empathy\MVC\Model;
use Empathy\ELib\Storage\BlogTag;
use Empathy\ELib\Storage\TagItem;
use Empathy\ELib\Storage\BlogItem;
use Empathy\ELib\Storage\BlogCategory;

class Service
{
    private static function getClient() {
        $hosts = defined('ELIB_ES_HOSTS')
            ? json_decode(ELIB_ES_HOSTS)
            : [];
        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }

    public static function processTags($b, $tags_arr, $cats_arr=array())
    {
        // deal with tags
        $bt = Model::load(BlogTag::class);
        $bt->removeAll($b->id);
        $t = Model::load(TagItem::class);

        if (strlen($_POST['tags']) > 0) {
            $tag_ids = $t->getIds($tags_arr, false);

            foreach ($tag_ids as $id) {
                $bt = Model::load(BlogTag::class);
                $bt->blog_id = $b->id;
                $bt->tag_id = $id;
                $bt->insert([], false);
            }
        }
        $t->cleanup();
    }

    public static function search($query)
    {
        $params = [
            'index' => 'elib_blog',
            'type' => 'blog',
            'body' => [
                'query' => [
                    'query_string' => [
                        'query' => $query
                    ]
                ],
                'size' => 250
            ]
        ];
        $client = self::getClient();
        $response = $client->search($params);
        return $response;
    }


    public function addAllToIndex()
    {
        $b = Model::load(BlogItem::class);
        $table =
        $all = $b->getAllCustom(' where status = ?' [\Empathy\ELib\Storage\BlogItemStatus::PUBLISHED]);
        $ids = array();
        foreach ($all as $item) {
            array_push($ids, $item['id']);
        }
        foreach ($ids as $id) {
            $b = Model::load(BlogItem::class);
            $b->load($id);
            self::addToIndex($b);
        }
    }

    public static function addToIndex($b)
    {
        if (defined('ELIB_BLOG_ELASTIC') && ELIB_BLOG_ELASTIC) {

            $bt = Model::load(BlogTag::class);
            $bc = Model::load(BlogCategory::class);
            $cats = $bc->getCategoriesForBlogItem($b->id);

            $cats_arr = array();
            foreach ($cats as $c) {
                $item = Model::load(BlogCategory::class);
                $item->load($c);
                array_push($cats_arr, $item->label);
            }

            $params = [
                'index' => 'elib_blog',
                'type' => 'blog',
                'id' => $b->id,
                'body' => [
                    'heading' => $b->heading,
                    'stamp' => $b->stamp,
                    'tags' => $bt->getTags($b->id),
                    'body' => strip_tags($b->body),
                    'slug' => $b->slug,
                    'categories' => $cats_arr
                ]
            ];

            //header('Content-type: application/json');
            //echo json_encode($params); exit();

            $client = self::getClient();
            $response = $client->index($params);
        }

    }

    public static function removeFromIndex($b)
    {
        if (defined('ELIB_BLOG_ELASTIC') && ELIB_BLOG_ELASTIC) {
            $params = [
                'index' => 'elib_blog',
                'type' => 'blog',
                'id' => $b->id
            ];

            $client = self::getClient();
            $response = $client->delete($params);
        }
    }

    public static function getMonthSlug($stamp)
    {
        return strtolower(substr(date('F', strtotime($stamp)), 0, 3));
    }

    public static function getHealth()
    {
        $client = self::getClient();
        return $response = $client->cat()->health();
    }
}