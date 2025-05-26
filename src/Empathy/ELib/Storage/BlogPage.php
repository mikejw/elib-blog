<?php

namespace Empathy\ELib\Storage;


use Empathy\MVC\Model;
use Empathy\MVC\RequestException;
use Empathy\ELib\Storage\BlogItem;
use Empathy\ELib\Storage\TagItem;
use Empathy\ELib\Storage\BlogCategory;
use Empathy\ELib\Storage\UserItem;
use Empathy\ELib\Storage\BlogComment;
use Empathy\ELib\Storage\BlogRevision;


class BlogPage
{
    private $blog_item;
    private $blog_user;
    private $blog_comments;
    private $page_title;

    private function notFound()
    {
        throw new RequestException('No blog item found', RequestException::NOT_FOUND);
    }

    public function __construct($id, $site_info = NULL, $preview = false)
    {
        $this->blog_item = Model::load(BlogItem::class);
        if (
            !$this->blog_item->load($id)
        ) {
            $this->notFound();
        } else {
            $r = Model::load(BlogRevision::class);
            list($this->blog_item) = $r->loadSaved($this->blog_item);
        }

        if (
            (!$preview && $this->blog_item->status != BlogItemStatus::PUBLISHED) ||
            ($preview && $this->blog_item->status != BlogItemStatus::DRAFT)
        ) {
            $this->notFound();
        }

        $t = Model::load(TagItem::class);
        $bc = Model::load(BlogCategory::class);

        if ($preview) {
            $cats = $bc->getAllCats(Model::getTable(BlogCategory::class), ' order by position');
        } else {
            $cats = $bc->getAllPublished(Model::getTable(BlogCategory::class), ' order by position');
        }

        $cats_lookup = array();
        foreach ($cats as $c) {
            $cats_lookup[$c['id']] = $c['label'];
        }

        $this->blog_item->setTags($t->getTagsForBlogItem($this->blog_item->id));
        $this->blog_item->setCats($bc->getCategoriesForBlogItem($this->blog_item->id));
        $cats = $this->blog_item->getCats();
        $cat_names = array();
        foreach ($cats as $c) {
            $cat_names[$c] = $cats_lookup[$c];
        }

        $this->blog_item->setCats($cat_names);

        $this->page_title = $this->blog_item->heading;

        if (is_object($site_info) && isset($site_info->title)) {
            $this->page_title .= ' - '.$site_info->title;
        }
        
        //$this->blog_item->body = preg_replace('/mid_/', 'tn_', $this->blog_item->body);
        $this->blog_user = Model::load(UserItem::class);
        $this->blog_user->load($this->blog_item->user_id);
        $this->blog_comments = $this->getCommentsFetch($this->blog_item->id);
        Model::disconnect(array($this->blog_item, $this->blog_user));
    }

    public function getAuthor()
    {
        return $this->blog_user->username;

    }

    public function getBody()
    {
        return $this->blog_item->body;
    }

    public function getHeading()
    {
        return $this->blog_item->heading;
    }

    public function getTitle()
    {
       return $this->page_title;
    }

    public function getBlogItem()
    {
        return $this->blog_item;
    }

    public function getComments()
    {
        return $this->blog_comments;
    }

    private function getCommentsFetch($id)
    {
        $params = [];
        $bc = Model::load(BlogComment::class);
        $sql = ' WHERE t1.user_id = t2.id';
        $sql .= ' AND t1.status = 1';
        $sql .= ' AND t1.blog_id = ?';
        $params[] = $id;
        $sql .= ' ORDER BY t1.stamp';
        return $bc->getAllCustomPaginateSimpleJoin(
            '*,t1.id AS id',
            Model::getTable(UserItem::class),
            $sql,
            1,
            200,
            '',
            $params
        );
    }
}
