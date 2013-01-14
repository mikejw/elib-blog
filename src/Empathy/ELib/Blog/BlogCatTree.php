<?php

namespace Empathy\ELib\Blog;

use Empathy\ELib\Tree;

class BlogCatTree extends Tree
{
    private $blog_category;
    private $data;
    private $blog_category_ancestors;
    

    public function getData() {
        return $this->data;
    }


    public function __construct($blog_category, $current_is_dir, $collapsed)
    {
        $this->blog_category = $blog_category;
        $this->blog_category_ancestors = array(0);
        $this->banner = new \stdClass(); // quick fix (code needs cleaning up)
        
        if ($current_is_dir) {
            $current_id = $this->blog_category->id;
            if (!$collapsed) {
                array_push($this->blog_category_ancestors, $this->blog_category->id);
            }
        } else {
            $current_id = $this->banner->id;
            array_push($this->blog_category_ancestors, $this->banner->blog_category_id);
        }

        $ancestors = array();
        if (!isset($this->blog_category->id)) {
            $ancestors = $this->blog_category->getAncestorIDs($this->banner->blog_category_id, $ancestors);
        } elseif ($this->blog_category->id != 0) {
            $ancestors = $this->blog_category->getAncestorIDs($this->blog_category->id, $ancestors);
        }
        if (sizeof($ancestors) > 0) {
            $this->blog_category_ancestors = array_merge($this->blog_category_ancestors, $ancestors);
        }

        $this->data = $this->buildTree(0, $this);

        $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0, 0, $current_is_dir);
    }

    public function buildTree($id, $tree)
    {
        $nodes = array();
        $nodes = $tree->blog_category->buildTree($id, $tree);
        
        return $nodes;
    }
    
    private function buildMarkup($data, $level, $current_id, $last_id, $last_node_data, $current_is_dir)
    {
        $markup = "\n<ul";
        
        $ancestors = $this->blog_category_ancestors;
        
        if (!in_array($last_id, $ancestors)) {
            $markup .= " class=\"hidden_sections\"";
        }
        if ($level == 0) {
            $markup .= " id=\"tree\"";
            $level++;
        }
        $markup .=">\n";
        foreach ($data as $index => $value) {
            $toggle = '+';
            $folder = 't_folder_closed.gif';
            $url = 'blog/category';
            
            if (in_array($value['id'], $ancestors)) {
                $toggle = '-';
                $folder = 't_folder_open.gif';
            }
            
            if (isset($value['banner']) && $value['banner'] == 1) {
                $folder = 'data.gif';
                $url = 'banner';
            }

            $children = sizeof($value['children']);
            $markup .= "<li";

            if ($current_id == $value['id'] && $value['banner'] != $current_is_dir) {
                $markup .= " class=\"current\"";
            }

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= "<a class=\"toggle\" href=\"http://".WEB_ROOT.PUBLIC_DIR."/admin/$url/".$value['id'];
                if ($toggle == '-') {
                    $markup .= '/?collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= "<span class=\"toggle\">&nbsp;</span>";
            }
            $markup .= "<img src=\"http://".WEB_ROOT.PUBLIC_DIR."/elib/$folder\" alt=\"\" />\n";

            if ($current_id == $value['id'] && $value['banner'] != $current_is_dir) {
                $markup .= "<span class=\"label current\">".$value['label']."</span>";
            } else {
                $markup .= "<span class=\"label\"><a href=\"http://".WEB_ROOT.PUBLIC_DIR."/admin/$url/".$value['id']."\">".$value['label']."</a></span>";
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id'], $value['banner'], $current_is_dir);
            }
            $markup .= "</li>\n";
        }
        $markup .= "</ul>\n";

        return $markup;
    }
}
