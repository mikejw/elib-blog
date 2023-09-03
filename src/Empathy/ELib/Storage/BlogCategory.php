<?php

/**
* Empathy/ELib/Storage/BlogCategory.php
*
* PHP Version 5
*
* LICENSE: This source file is subject to the LGPLv3 License that is bundled
* with this source code in the file licence.txt
*
* @category  Application
* @package   ELib_Blog
* @author    Mike Whiting <mail@mikejw.co.uk>
* @copyright 2008-2013 Mike Whiting
* @license   http://www.gnu.org/licenses/gpl-3.0-standalone.html GPL v3.0
* @link      http://empathyphp.co.uk
*
*/

namespace Empathy\ELib\Storage;
use Empathy\ELib\Model,
    Empathy\MVC\Entity;

/**
* Empathy CLI Utility class.
* Make various requests to the MVC from the command line.
*
* @category Application
* @package  ELib_Blog
* @author   Mike Whiting <mail@mikejw.co.uk>
* @license  http://www.gnu.org/licenses/gpl-3.0-standalone.html GPL v3.0
* @link     http://empathyphp.co.uk
*/
class BlogCategory extends Entity
{
    const TABLE = 'blog_category';

    public $id;
    public $blog_category_id;
    public $label;


    /**
    * Get list of all categories that only
    * have published blogs
    *
    * @param string $sql_string
    *
    * @return array
    */
    public function getAllPublished($table, $sql_string, $authorId = null)
    {
        $queryParams = array();
        $all = array();
        //$sql = "select c.id, c.label, b.status, b.id, b.heading, j.blog_category_id"
        $sql = "select c.id, c.label"
            ." from %s b"
            ." left join %s j on j.blog_id = b.id"
            ." left join %s c on c.id = j.blog_category_id"
            ." where b.status = ?";

        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (!is_null($authorId)) {
            $sql .= ' AND b.user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= " group by j.blog_category_id".$sql_string;

        $sql = sprintf($sql, Model::getTable('BlogItem'),
            Model::getTable('BlogItemCategory'),
            Model::getTable('BlogCategory'));
        $error = 'Could not published categories.';
        $result = $this->query($sql, $error, $queryParams);

        $i = 0;
        foreach ($result as $row) {
            $all[$i] = $row;
            $i++;
        }

        return $all;
    }


    /**
    * Get category ids associated with blog item
    *
    * @param integer $blog_id blog id
    *
    * @return array
    */
    public function getCategoriesForBlogItem($blog_id)
    {
        $categories = array();
        $sql = 'SELECT id FROM '.self::TABLE.' c'
            .', '.Model::getTable('BlogItemCategory').' b'
            .' WHERE b.blog_category_id = c.id'
            .' AND b.blog_id = ?';
        $error = 'Could not get categories for blog item.';
        $result = $this->query($sql, $error, array($blog_id));
        foreach ($result as $row) {
            $categories[] = $row['id'];
        }

        return $categories;
    }


    /**
    * Remove associations to any category for blog item
    *
    * @param integer $blog_id blog id
    *
    * @return void
    */
    public function removeForBlogItem($blog_id)
    {
        $sql = 'DELETE FROM '.Model::getTable('BlogItemCategory')
            .' WHERE blog_id = ?';
        $error = 'Could not clear categories associated with blog item.';
        $this->query($sql, $error, array($blog_id));
    }


    /**
    * Associate blog item with one or more categories
    *
    * @param array   $categories categories to be associated
    * @param integer $blog_id    blog item id
    *
    * @return void
    */
    public function createForBlogItem($categories, $blog_id)
    {
        $bc = Model::load('BlogItemCategory');
        foreach ($categories as $cat) {
            $bc->blog_id = $blog_id;
            $bc->blog_category_id = $cat;
            $bc->insert(Model::getTable('BlogItemCategory'), false, array(), 1);
        }
    }


    /**
    * Validate model object.
    *
    * @return void
    */
    public function validates()
    {
        if ($this->label == '' || !ctype_alnum(str_replace(' ', '', $this->label))) {
            $this->addValError('Invalid label');
        }
    }


    /**
    * Recursive function to build data structure for the blog category admin page
    *
    * @param integer $current root node of data structure
    * (set to null when equal to zero)
    *
    * @param Tree    $tree    tree object
    *
    * @return array  $nodes   data structure
    */
    public function buildTree($current, $tree)
    {
        $i = 0;
        $nodes = array();
        $queryParams = array();
        if ($current == 0) {
            $sql = 'SELECT id,label FROM '.Model::getTable('BlogCategory')
                .' WHERE blog_category_id IS NULL';
        } else {
            $sql = 'SELECT id,label FROM '.Model::getTable('BlogCategory')
                .' WHERE blog_category_id = ?';
            array_push($queryParams, $current);
        }

        $error = 'Could not get child blog categories.';
        $result = $this->query($sql, $error, $queryParams);
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



    /**
    * Recursively get all ancestor category ids
    *
    * @param integer $id        the category node to begin from
    * @param array   $ancestors the ancestors array
    *
    * @return array $ancestors the ancestors
    */
    public function getAncestorIDs($id, $ancestors)
    {
        $section_id = 0;
        $sql = 'SELECT blog_category_id FROM '
            .Model::getTable('BlogCategory')
            .' WHERE id = ?';
        $error = 'Could not get parent id from blog category.';
        $result = $this->query($sql, $error, array($id));
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


    /**
    * Check to see if a category has children (categories)
    *
    * @param integer $id the potential parent category
    *
    * @return bool $cats 
    */
    public function hasCats($id)
    {
        $cats = false;
        $sql = 'SELECT id FROM '.Model::getTable('BlogCategory')
            .' WHERE blog_category_id = ?';
        $error = 'Could not check for existing child categories.';
        $result = $this->query($sql, $error, array($id));
        if ($result->rowCount() > 0) {
            $cats = true;
        }

        return $cats;
    }


    /**
    * Get category id by label
    *
    * @param string $label the category label
    *
    * @return integer $cat the category id (zero if not found)
    */
    public function getIdByLabel($label)
    {
        $cat = 0;
        $sql = 'SELECT id from '.Model::getTable('BlogCategory')
            .' WHERE label like ?';
        $error = 'Could not get current category id.';
        $result = $this->query($sql, $error, array('%' . $label . '%'));
        if ($result->rowCount() == 1) {
            $row = $result->fetch();
            $cat = $row['id'];
        }
        return $cat;
    }
}
