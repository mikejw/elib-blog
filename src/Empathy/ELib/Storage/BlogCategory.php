<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\Entity;

class BlogCategory extends Entity
{
    const TABLE = 'blog_category';

    public $id;
    public $blog_category_id;
    public $label;

    public function getCategoriesForBlogItem($blog_id)
    {
        $categories = array();
        $sql = 'SELECT id FROM '.self::TABLE.' c'
            .', '.Model::getTable('BlogItemCategory').' b'
            .' WHERE b.blog_category_id = c.id'
            .' AND b.blog_id = '.$blog_id;
        $error = 'Could not get categories for blog item.';
        $result = $this->query($sql, $error);
        foreach ($result as $row) {
            $categories[] = $row['id'];
        }

        return $categories;
    }

    public function removeForBlogItem($blog_id)
    {
        $sql = 'DELETE FROM '.Model::getTable('BlogItemCategory').' WHERE blog_id = '.$blog_id;
        $error = 'Could not clear categories associated with blog item.';
        $this->query($sql, $error);
    }

    public function createForBlogItem($categories, $blog_id)
    {
        $bc = Model::load('BlogItemCategory');
        foreach ($categories as $cat) {
            $bc->blog_id = $blog_id;
            $bc->blog_category_id = $cat;
            $bc->insert(Model::getTable('BlogItemCategory'), false, array(), 1);
        }
    }

    public function validates()
    {
        if ($this->label == '' || !ctype_alnum(str_replace(' ', '', $this->label))) {
            $this->addValError('Invalid label');
        }
    }

    public function buildTree($current, $tree)
    {
        $i = 0;
        $nodes = array();
        $sql = 'SELECT id,label FROM '.Model::getTable('BlogCategory').' WHERE blog_category_id = '.$current;
        $error = 'Could not get child blog categories.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $nodes[$i]['id'] = $id;
                $nodes[$i]['banner'] = 0;
                $nodes[$i]['label'] = $row['label'];
                $nodes[$i]['children'] = $tree->buildTree($id, $tree);
                $i++;
            }
        }

        return $nodes;
    }

    public function getAncestorIDs($id, $ancestors)
    {
        $section_id = 0;
        $sql = 'SELECT blog_category_id FROM '.Model::getTable('BlogCategory').' WHERE id = '.$id;
        $error = 'Could not get parent id from blog category.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $blog_category_id = $row['blog_category_id'];
        }
        if (isset($blog_category_id) && $blog_category_id != 0) {
            array_push($ancestors, $blog_category_id);
            $ancestors = $this->getAncestorIDs($blog_category_id, $ancestors);
        }

        return $ancestors;
    }

    public function hasCats($id)
    {
        $cats = 0;
        $sql = 'SELECT id FROM '.Model::getTable('BlogCategory').' WHERE blog_category_id = '.$id;
        $error = 'Could not check for existing child categories.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $cats = 1;
        }

        return $cats;
    }

    public function getIdByLabel($label)
    {
        $cat = 0;
        $sql = 'SELECT id from '.Model::getTable('BlogCategory').' WHERE label like \'%'.$label.'%\'';
        $error = 'Could not get current category id.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $cat = $row['id'];
        }
        return $cat;
    }
}
