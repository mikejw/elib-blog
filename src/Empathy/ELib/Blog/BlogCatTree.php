<?php

/**
* Empathy/ELib/Blog/BlogCatTree.php
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

namespace Empathy\ELib\Blog;

use Empathy\ELib\Tree;
use Empathy\MVC\Config;

/**
* Class for generating blog categories tree data
*
* @category Application
* @package  ELib_Blog
* @author   Mike Whiting <mail@mikejw.co.uk>
* @license  http://www.gnu.org/licenses/gpl-3.0-standalone.html GPL v3.0
* @link     http://empathyphp.co.uk
*/
class BlogCatTree extends Tree
{
    private $_blogCategory;
    private $_data;
    private $_blogCategoryAncestors;
    private $banner;
    

    public function getData() {
        return $this->_data;
    }


    public function getBlogCategoryObject()
    {

        return $this->_blogCategory;
    }

    public function __construct($blog_category)
    {
       

        $this->_blogCategory = $blog_category;
        $this->_blogCategoryAncestors = array(0);
        $this->banner = new \stdClass(); // quick fix (code needs cleaning up)
        
      
        $current_id = $this->_blogCategory->id;
        array_push($this->_blogCategoryAncestors, $this->_blogCategory->id);

        
        $ancestors = array();
       
        if ($this->_blogCategory->id != 0) {

            $ancestors = $this->_blogCategory->getAncestorIDs($this->_blogCategory->id, $ancestors);
        }
        if (sizeof($ancestors) > 0) {

            $this->_blogCategoryAncestors = array_merge($this->_blogCategoryAncestors, $ancestors);
        }

        $this->_data = $this->buildTree(0, $this);

        $this->markup = $this->buildMarkup($this->_data, 0, $current_id, 0, 0);
    }

    public function buildTree($id, $tree)
    {
        $nodes = array();
        $nodes = $tree->getBlogCategoryObject()->buildTree($id, $tree);
        
        return $nodes;
    }
    
    private function buildMarkup($data, $level, $current_id, $last_id, $last_node_data)
    {
        $markup = "\n<ul data-controller=\"admin/blog/cat_sort\" class=\"clearfix";
        $ancestors = $this->_blogCategoryAncestors;
        
        if (!in_array($last_id, $ancestors)) {
            $markup .= " hidden_sections";
        }

        $markup .= "\"";
        if ($level == 0) {
            $markup .= " id=\"tree\"";
            $level++;
        }
        $markup .=">\n";
        foreach ($data as $index => $value) {
            $toggle = '+';
            $folder = '<i class="far fa-folder"></i>';
            $url = 'blog/category';
            
            if (in_array($value['id'], $ancestors)) {
                $toggle = '-';
                $folder = '<i class="far fa-folder-open"></i>';
            }
            
            if (isset($value['banner']) && $value['banner'] == 1) {
                $folder = '<i class="far fa-file"></i>';
                $url = 'banner';
            }

            $children = sizeof($value['children']);
            $class = "clearfix";
            $markup .= "<li";

            $markup .= " id=\"category_".$value['id']."\"";

            if ($current_id == $value['id']) {
                $class .= " current";
            }
            $markup .= " class=\"$class\"";

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= "<a class=\"toggle\" href=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'];
                if ($toggle == '-') {
                    $markup .= '/?collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= "<span class=\"toggle\">&nbsp;</span>";
            }
            $markup .= $folder;

            if ($current_id == $value['id']) {
                $markup .= "<span class=\"label current\">".$value['label']."</span>";
            } else {
                $markup .= "<span class=\"label\"><a href=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id']."\">".$value['label']."</a></span>";
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id'], $value['banner']);
            }
            $markup .= "</li>\n";
        }
        $markup .= "</ul>\n";

        return $markup;
    }
}
