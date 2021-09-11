<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\RequestException;



class BlogPage
{
    private $blog_item;
    private $blog_user;
    private $blog_comments;
    private $page_title;

    public function __construct($id, $site_info = NULL)
    {
        $this->blog_item = Model::load('BlogItem');
        $this->blog_item->id = $id;
        if (!$this->blog_item->load()) {
            throw new RequestException('No blog item found', RequestException::NOT_FOUND);
        }

        $this->page_title = $this->blog_item->heading;

        if (is_object($site_info)) {
            $this->page_title .= ' - '.$site_info->title;
        }
        
        //$this->blog_item->body = preg_replace('/mid_/', 'tn_', $this->blog_item->body);
        $this->blog_user = Model::load('UserItem');
        $this->blog_user->id = $this->blog_item->user_id;
        $this->blog_user->load();
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
        $bc = Model::load('BlogComment');
        $sql = ' WHERE t1.user_id = t2.id';
        $sql .= ' AND t1.status = 1';
        $sql .= ' AND t1.blog_id = '.$id;
        $sql .= ' ORDER BY t1.stamp';
        return $bc->getAllCustomPaginateSimpleJoin('*,t1.id AS id', Model::getTable('BlogComment'), Model::getTable('UserItem'), $sql, 1, 200);
    }
}
