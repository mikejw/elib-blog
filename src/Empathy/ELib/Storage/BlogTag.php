<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\BlogTag as EBlogTag;
use Empathy\ELib\Storage\TagItem;
use Empathy\ELib\Storage\BlogItem;


class BlogTag extends Entity
{
    const TABLE = 'blog_tag';

    public $blog_id;
    public $tag_id;

    public function removeAll($blog_id)
    {
        $sql = 'DELETE FROM '.Model::getTable(EBlogTag::class).' WHERE blog_id = ?';
        $error = 'Could not clear existing tags for blog item.';
        $this->query($sql, $error, array($blog_id));
    }

    public function getTags($blog_id)
    {
        $tags = array();
        $sql = 'SELECT t.tag FROM '.Model::getTable(TagItem::class).' t, '
            .Model::getTable(BlogTag::class).' b WHERE t.id = b.tag_id AND b.blog_id = ?';
        $error = 'Could not get tags.';
        $result = $this->query($sql, $error, array($blog_id));
        $i = 0;
        foreach ($result as $row) {
            $tags[$i] = $row['tag'];
            $i++;
        }

        return $tags;
    }

    public function getBlogs($tags)
    {
        $queryParams = array();
        $id = array();
        $sql = 'SELECT DISTINCT b.id FROM '.Model::getTable(BlogItem::class).' b';
        $i = 0;
        foreach ($tags as $tag) {
            $glue = 't'.($i + 1);
            $sql .= ' LEFT JOIN '.Model::getTable(EBlogTag::class).' '.$glue.' ON '.$glue.'.tag_id = ?';
            array_push($queryParams, $tag);
            $i++;
        }
        $i = 0;
        foreach ($tags as $tag) {
            $glue = 't'.($i + 1);
            if ($i == 0) {
                $sql .= ' WHERE';
            } else {
                $sql .= ' AND';
            }
            $sql .= ' b.id = '.$glue.'.blog_id';
            $i++;
        }

        $error = 'Could not get active blog ids.';
        $result = $this->query($sql, $error, $queryParams);
        $i = 0;
        foreach ($result as $row) {
            $id[$i] = $row['id'];
            $i++;
        }

        return $id;
    }
}
