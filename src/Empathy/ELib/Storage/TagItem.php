<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model;
use Empathy\MVC\Entity;
use Empathy\MVC\DI;


class TagItem extends Entity
{
    const TABLE = 'tag';

    public $id;
    public $tag;

    public function getTagsForBlogItem($blog_id)
    {
        $tags = array();
        $sql = 'SELECT tag FROM '.self::TABLE.' t'
            .', '.Model::getTable('BlogTag').' b'
            .' WHERE b.tag_id = t.id'
            .' AND b.blog_id = ?';
        $error = 'Could not get tags for blog item.';
        $result = $this->query($sql, $error, array($blog_id));
        foreach ($result as $row) {
            $tags[] = $row['tag'];
        }

        return $tags;
    }

    public function getIds($tags, $locked)
    {
        $table = Model::getTable('TagItem');
        $ids = array();
        $i = 0;
        foreach ($tags as $tag) {
            $sql = 'SELECT id FROM '.$table.' WHERE tag = ?';
            $error = 'Could not check for tag id.';
            $result = $this->query($sql, $error, array($tag));
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                $id = $row['id'];
                $ids[$i] = $id;
            } elseif (!($locked)) {
                $sql = 'INSERT INTO '.$table.' VALUES(NULL, ?)';
                $error = 'Could not insert tag.';
                $result = $this->query($sql, $error, array($tag));
                $ids[$i] = $this->insertId();
            }
            $i++;
        }

        return $ids;
    }

    public function getAllTags($category_id, $authorId = null)
    {
        $queryParams = array();
        $category_id = (int) $category_id;
        $total = 0;
        $sql = 'SELECT COUNT(b.blog_id) AS count FROM '.Model::getTable('BlogTag').' b,'
            .Model::getTable('BlogItem').' c';

        if ($category_id > 0) {
            $sql .= ', '.Model::getTable('BlogItemCategory').' d';
        }

        $sql .= ' WHERE c.id = b.blog_id AND c.status = ?';
        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if ($category_id > 0) {
            $sql .= ' AND d.blog_category_id = ?'
                .' AND d.blog_id = b.blog_id';
            array_push($queryParams, $category_id);
        }

        if (!is_null($authorId)) {
            $sql .= ' AND c.user_id = ?';
            array_push($queryParams, $authorId);
        }

        $error = 'Could not get total number of tagging instances';
        $result = $this->query($sql, $error, $queryParams);
        $row = $result->fetch();
        $total = $row['count'];

        $queryParams = array();
        $tag = array();
        $sql = 'SELECT t.tag, COUNT(b.blog_id) AS count FROM '.Model::getTable('BlogItem').' c, '.Model::getTable('TagItem').' t LEFT JOIN '.Model::getTable('BlogTag')
            .' b ON (b.tag_id = t.id)';

        if ($category_id > 0) {
            $sql .= ', '.Model::getTable('BlogItemCategory').' d';
        }
        $sql .= ' WHERE c.status = ? AND b.blog_id = c.id';
        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if ($category_id > 0) {
            $sql .= ' AND d.blog_category_id = ?'
                .' AND d.blog_id = b.blog_id';
            array_push($queryParams, $category_id);
        }

        if (!is_null($authorId)) {
            $sql .= ' AND c.user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= ' GROUP BY t.id';

        $error = 'Could not get all active tags';
        $result = $this->query($sql, $error, $queryParams);
        $i = 0;
        foreach ($result as $row) {
            $tag[$i] = $row;
            $share = ceil(100 / $total * $tag[$i]['count']);
            $tag[$i]['share'] = $share;
            $i++;
        }

        return $tag;
    }

    public function cleanup()
    {
        $current = array();
        $sql = 'SELECT DISTINCT b.tag_id FROM '.Model::getTable('BlogTag').' b';
        $error = 'Could not get all current tag ids.';
        $result = $this->query($sql, $error);

        $i = 0;
        foreach ($result as $row) {
            $current[$i] = $row['tag_id'];
            $i++;
        }

        $stored = array();
        $sql = 'SELECT t.id FROM '.Model::getTable('TagItem').' t';
        $error = 'Could not get all tag ids';
        $result = $this->query($sql, $error);
        $i = 0;
        foreach ($result as $row) {
            $stored[$i] = $row['id'];
            $i++;
        }

        $dumped = array();

        foreach ($stored as $item) {
            if (!in_array($item, $current)) {
                array_push($dumped, $item);
            }
        }

        if ($dumped != '(0)') {
            list($unionSql, $params) = $this->buildUnionString($dumped);
            $sql = 'DELETE FROM '.Model::getTable('TagItem').' WHERE id IN ' . $unionSql;
            $error = 'Could not remove redundant tags.';
            $this->query($sql, $error, $params);
        }
    }

}
