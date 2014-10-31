<?php

namespace Empathy\ELib\Blog;

use Empathy\ELib\Model,
    Empathy\ELib\EController,
    Empathy\ELib\User\CurrentUser,
    Empathy\MVC\Session,
    Empathy\MVC\RequestException,
    Empathy\ELib\Storage\BlogPage;

class BlogFrontControllerNew extends EController
{
    private $cache;

    public function __construct($boot)
    {
        parent::__construct($boot);
        $this->cache = $this->stash->get('cache');
    }


    public function default_event()
    {
        $b = Model::load('BlogItem');
        $blogs = array();

        $sql = '';

        $active_tags = array();
        $active_tags_string = '';

        $found_items = '(0,)';

        if (isset($_GET['active_tags'])) {
            $found_items = $this->getActiveTags();
        }

        if (!isset($_GET['id'])) {
            $_GET['id'] = 1;
        }
        $page = $_GET['id'];

        $blogs = $this->getBlogs($b, $found_items, $page);

        $this->assign('page', $page);
        $this->assign('pages', $b->getPages());
        $this->assign('total_pages', sizeof($b->getPages()));

        $this->getAvailableTags();
        $this->getArchive();
        $cats = $this->getCategories();

        $cats_lookup = array();
        foreach ($cats as $c) {

            $cats_lookup[$c['id']] = $c['label'];
        }

        foreach ($blogs as &$b) {
            $cats = $b['cats'];

            $cat_names = array();
            foreach ($cats as $c) {
                $cat_names[] = $cats_lookup[$c];
            }
            $b['cats'] = $cat_names;
        }

        $this->assign('blogs', $blogs);

        // header('Content-type: text/json');
        // echo json_encode($blogs, JSON_PRETTY_PRINT);
        // exit();

        $this->assign('current_year', date('Y', time()));
        $this->assign('current_month', date('F', time()));
        if(defined('ELIB_BLOG_MODULE')) {
            $this->assign('blog_module', ELIB_BLOG_MODULE);
        } else {
            $this->assign('blog_module', 'blog');
        }
    }   


    public function getBlogPageData($id)
    {
        return new BlogPage($id, $this->stash->get('site_info'));
    }


    public function getBlogIdBySlug($slug_arr)
    {
        $b = Model::load('BlogItem');
        return $b->findByArchiveURL($this->convertMonth($slug_arr['month']), $slug_arr['year'], $slug_arr['day'], $slug_arr['slug']);
    }

    public function item()
    {
        if (isset($_POST['submit'])) {
            $this->submitComment();
        }

        if (isset($_GET['id']) && $_GET['id'] == 0) {

            $slug_arr = array(
                'month' => $_GET['month'],
                'year' => $_GET['year'],
                'day' => $_GET['day'],
                'slug' => $_GET['slug']);
            $slug_key = 'blog_item_'.implode('_', $slug_arr);
            $_GET['id'] = $this->cache->cachedCallback($slug_key, array($this, 'getBlogIdBySlug'), array($slug_arr)); 
        }

        if (!$this->initID('id', -1, true)) {
            throw new RequestException('No valid blog id', RequestException::BAD_REQUEST);
        }

        $id = $_GET['id'];
        $blog_page = $this->cache->cachedCallback('blog_'.$id, array($this, 'getBlogPageData'), array($id));


        $this->assign('slug_arr', $slug_arr);
        $this->assign('author', $blog_page->getAuthor());
        $this->assign('blog', $blog_page->getBlogItem());
        $this->assign('custom_title', $blog_page->getTitle());
        $this->assign('custom_description', $blog_page->getBody());
        //$this->assign('comments', $blog_page->getComments());

        $this->getAvailableTags();
        $this->getArchive();
        $cats = $this->getCategories();
        $this->setTemplate('blog_item.tpl');

        $bi = Model::load('BlogImage');
        $b_ids = array($id);
        $blog_images = $bi->getForIDs($b_ids);
        if (sizeof($blog_images)) {
            $this->assign('primary_image', $blog_images[$id][0]['filename']);
        }

        $bc = Model::load('BlogCategory');
        $blog_cats = $bc->getCategoriesForBlogItem($id);
         
        $cats_lookup = array();
        foreach ($cats as $c) {
            $cats_lookup[$c['id']] = $c['label'];
        }
        if (sizeof($blog_cats)) {
            $this->assign('sample_category', $cats_lookup[$blog_cats[0]]);    
        }
    }






    public function year()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $b = Model::load('BlogItem');
            $months = $b->getYear($_GET['id']);
            $this->presenter->assign('months', $months);
            $this->presenter->assign('year', $_GET['id']);
            $this->presenter->assign('custom_title', "Archive for ".$_GET['id']." - Mike Whiting's Blog");
        }
        $this->setTemplate('blog_year.tpl');
    }


    public function month()
    {
        if(isset($_GET['month']) && $_GET['month'] != ''
           && isset($_GET['year']) && is_numeric($_GET['year']))
        {
            $year = $_GET['year'];
            $m = $this->convertMonth($_GET['month']);

            $b = Model::load('BlogItem');
            $blogs = $b->getMonth($m, $year);

            foreach ($blogs as $index => $item) {
                $blogs[$index]['month_slug'] = strtolower(substr(date("F", $item['stamp']), 0, 3));
            }

            $month = $b->getMonthName($m, $year);

            $this->presenter->assign('month', $month);
            $this->presenter->assign('month_slug', substr(strtolower($month), 0, 3));
            $this->presenter->assign('year', $year);

            $this->presenter->assign('custom_title', "Archive for $month $year - Mike Whiting's Blog");

            $this->presenter->assign('blogs', $blogs);
        }
        $this->setTemplate('blog_month.tpl');
    }



    public function day()
    {
        if(isset($_GET['month']) && $_GET['month'] != ''
           && isset($_GET['year']) && is_numeric($_GET['year'])
           && isset($_GET['day']) && is_numeric($_GET['day']))
        {
            $year = $_GET['year'];
            $m = $this->convertMonth($_GET['month']);
            $day = $_GET['day'];

            $b = Model::load('BlogItem');
            $blogs = array();

            if (!checkdate($m, $day, $year)) {
                throw new RequestException('Not a valid date', RequestException::BAD_REQUEST);
            } else {
                $blogs = $b->getDay($m, $year, $day);
            }

            // copied from default_event
            foreach ($blogs as $index => $item) {
                $body_arr = array();
                $body_new = array();
                $i = 0;

                $body = $item['body'];
                $body_arr = preg_split('/<\/p>\s+<p>/', $body);
                if (sizeof($body_arr) > 2) {
                    while ($i < 2) {
                        array_push($body_new, $body_arr[$i]);
                        $i++;
                    }
                    $blogs[$index]['body'] = implode($body_new, '</p><p>').'</p>';
                    $blogs[$index]['truncated'] = 1;
                } else {
                    $blogs[$index]['truncated'] = 0;
                }
                $blogs[$index]['month_slug'] = strtolower(substr(date("F", $item['stamp']), 0, 3));
            }

            $month = $b->getMonthName($m, $year);
            $this->presenter->assign('month', $month);
            $this->presenter->assign('month_slug', substr(strtolower($month), 0, 3));
            $this->presenter->assign('year', $year);
            $this->presenter->assign('day', preg_replace('/^0+/', '', $day));

            $date = mktime(0, 0, 0, $m, $day, $year);
            $suffix = date("S", $date);
            $day_name = date("l", $date);
            $this->presenter->assign('suffix', $suffix);
            $this->presenter->assign('day_name', $day_name);

            $this->presenter->assign('custom_title', "Archive for $day_name, "
                                     .preg_replace('/^0+/', '', $day)."$suffix $month $year");

            $this->presenter->assign('blogs', $blogs);
        }
        $this->setTemplate('blog_day.tpl');
    }


    public function fetchCategoryId($cat)
    {
        $id = 0;
        if($cat != 'any') {
            $c = Model::load('BlogCategory');        
            if(0 === $id = $c->getIdByLabel($cat)) {
                throw new RequestException('Not a valid category', RequestException::BAD_REQUEST);
            }
        }
        return $id;
    }


    public function category()
    {
        $cat_id = $this->cache->cachedCallback('category_'.$_GET['category'],
                                               array($this, 'fetchCategoryId'), array($_GET['category']));
        Session::set('blog_category', $cat_id); // may not work without cookies but setting anyway
        $this->stash->store('blog_category', $cat_id);
        $this->assign('blog_category', $cat_id);
        $this->default_event();

    }


    public function set_category()
    {        
        Session::set('blog_category',
                     $this->cache->cachedCallback('category_'.$_GET['category'],
                     array($this, 'fetchCategoryId'), array($_GET['category'])));
        $this->redirect('');
    }


    public function tags()
    {
        if (!isset($_GET['active_tags'])) {
            $this->redirect('');
        }
        $_GET['active_tags'] = $this->getTags();
        $this->default_event();
    }




    public function feed()
    {
        header("Content-type: text/xml");
        echo $this->cache->cachedCallback('blog_feed', array($this, 'getBlogFeed'));
        exit();
    }

    public function getBlogFeed()
    {
        //$title = TITLE.' RSS Feed';
        $info = $this->stash->get('site_info');
        $title = $info->title;
        $link = 'http://'.WEB_ROOT.PUBLIC_DIR;
        $description = ELIB_BLOG_DESCRIPTION;
        $language = 'en-us';

        $content = "<rss version=\"2.0\">\n\t<channel>\n\t\t<title>$title</title>\n\t\t<link>$link</link>\n\t\t"
            ."<description>$description</description>\n\t\t<language>$language</language>\n\t</channel>\n</rss>";

        $xml = new \SimpleXMLElement($content);

        $b = Model::load('BlogItem');
        $blogs = $b->getFeed();

        foreach ($blogs as $item) {
            $child = $xml->channel->addChild('item');
            $child->addChild('title', $item['heading']);
            $child->addChild('link', 'http://'.WEB_ROOT.PUBLIC_DIR.'/blog/item/'.$item['id']);
            $child->addChild('pubDate', date('r', $item['stamp']));
            $utf_string = mb_convert_encoding($item['body'], 'UTF-8', 'HTML-ENTITIES');
            $child->addChild('description', $this->truncate(strip_tags($utf_string), 250));
        }

        return $xml->asXML();
    }






    private function submitComment()
    {
        $bc = Model::load('BlogComment');
        $bc->blog_id = $_GET['id'];
        $bc->status = 1;
        $bc->body = $_POST['body'];
        $bc->heading = '';
        $bc->user_id = CurrentUser::getUserId();
        $bc->validates();

        if ($bc->hasValErrors()) {
            $this->presenter->assign('comment', $bc);
            $this->presenter->assign('errors', $bc->val->errors);
        } else {
            $bc->stamp = date('Y-m-d H:i:s', time());
            $bc->insert(Model::getTable('BlogComment'), 1, array('body'), 1);
            $this->redirect('blog/item/'.$bc->blog_id);
        }
    }

   

    private function getTags()
    {      
        return explode('+', urlencode($_GET['active_tags']));
    }

    private function setTagsTitle()
    {
        $title = 'Items tagged ';
        $i = 0;
        foreach ($_GET['active_tags'] as $tag) {
            $title .= '"'.$tag.'" ';
            if ($i+1 != sizeof($_GET['active_tags'])) {
                $title .= 'and ';
            }
            $i++;
        }
        $info = $this->stash->get('site_info');
        $title .= 'in '.$info->title;
        $this->assign('custom_title', $title);
    }

/*
    private function cachedCallback($key, $callback, $callback_params=array(), $setOnFail=true)
    {
        $data = false;
        if($this->caching && (false != ($data = $this->cache->get($key)))) {

            // received cached
        } else {
                $data = call_user_func_array($callback, $callback_params);
                if($setOnFail) {
                    $this->cache->set($key, $data);    
                }                
        }
        return $data;
    }
    */


    private function getAvailableTags()
    {
        $tags = $this->cache->cachedCallback('tags', array($this, 'getAvailableTagsFetch'));
        $this->assign('tags', $tags);
    }


    public function getAvailableTagsFetch()
    {
        $t = Model::load('TagItem');
        $tags = $t->getAllTags();

        foreach ($tags as $index => $item) {
            $tags[$index]['tag_esc_1'] = '/\+'.$tags[$index]['tag'].'/';
            $tags[$index]['tag_esc_2'] = '/'.$tags[$index]['tag'].'\+/';
            $tags[$index]['share'] = ($tags[$index]['share'] / 10) * 2.5;
        }
        return $tags;       
    }

    private function getArchive()
    {
        $b = Model::load('BlogItem');
        $bc = $this->stash->get('blog_category');
        $archive = $this->cache->cachedCallback('archive_'.$bc, array($b, 'getArchive'), array($bc));
        $this->assign('archive', $archive); 
    }

    private function getCategories()
    {
        $c = Model::load('BlogCategory');
        $cats = $this->cache->cachedCallback('cats', array($c, 'getAllCustom'), array(Model::getTable('BlogCategory'), ' order by id'));
        array_push($cats, array('id' => 0, 'label' => 'Any'));        
        $this->assign('categories', $cats);
        
        return $cats;
    }


    private function getBlogs($b, $found_items, $page)
    {
        $bc = $this->stash->get('blog_category');
        
        $blogs = $b->getItems($found_items, ELIB_BLOG_ENTRIES, $bc, $page);

        $t = Model::load('TagItem');
        $bc = Model::load('BlogCategory');

        foreach ($blogs as &$b_item) {
            $b_item['tags'] = $t->getTagsForBlogItem($b_item['blog_id']);
            $b_item['month_slug'] = strtolower(substr(date("F", $b_item['stamp']), 0, 3));
            $b_item['cats'] = $bc->getCategoriesForBlogItem($b_item['blog_id']);
        }

        if(defined('ELIB_TRUNCATE_BLOG_ITEMS') &&
           ELIB_TRUNCATE_BLOG_ITEMS == true)
        {
            foreach ($blogs as $index => $item) {
                $body_arr = array();
                $body_new = array();
                $i = 0;

                $body = $item['body'];
                $body_arr = preg_split('/<p>/', $body);

                if (sizeof($body_arr) > 2) {
                    while ($i < 2) {
                        array_push($body_new, $body_arr[$i]);
                        $i++;
                    }
                    $blogs[$index]['body'] = implode($body_new, '<p>');
                    $blogs[$index]['truncated'] = 1;
                } else {
                    $blogs[$index]['truncated'] = 0;
                }
            }
        }

        // fetch all images associated with each blog item
        if(defined('ELIB_FETCH_BLOG_IMAGES') &&
           ELIB_FETCH_BLOG_IMAGES == true)
        {
            $bi = Model::load('BlogImage');
            $b_ids = array();
            foreach ($blogs as $item) {
                array_push($b_ids, $item['blog_id']);
            }
            $blog_images = $bi->getForIDs($b_ids);

            foreach ($blogs as $index => $item) {
                $id = $item['blog_id'];
                if (isset($blog_images[$id])) {
                    $blogs[$index]['image'] = $blog_images[$id];
                }
            }
            $this->assign('blog_images', $blog_images);
        }

        return $blogs;
    }



    public function findBlogsByTags($active_tags)
    {
        $t = Model::load('TagItem');
        $bt = Model::load('BlogTag');  
        $tags = $t->getIds($active_tags, true);
        if(sizeof($tags) != sizeof($active_tags)) {
            return false; // contains invalid tags
        } else {
            return $bt->buildUnionString($bt->getBlogs($tags));
        }
      
    }

    private function getActiveTags()
    {
        $active_tags = $_GET['active_tags'];
        $active_tags_string = implode('+', $active_tags);

        $key = implode('_', $active_tags);
        $found_items = $this->cache->cachedCallback('blogs_by_tag_'.$key, array($this, 'findBlogsByTags'), array($active_tags));

        if ($found_items == '(0,)' || $found_items == false) {

            throw new RequestException('Not found.', RequestException::NOT_FOUND);

        } else {
            $this->setTagsTitle();
        }

        $this->assign('active_tags', $active_tags);
        $this->assign('active_tags_string', $active_tags_string);
        $this->assign('multi_tags', strpos($active_tags_string, '+') !== false);

        return $found_items;
    }





    public function truncate($desc, $max_length)
    {
        if (strlen($desc) > $max_length) {
            $char = 'A';
            if (preg_match('/ /', substr($desc, 0, $max_length))) { // do trunc
                //while($max_length > 0 && $char != ' ')
                while (preg_match('/\w/', $char)) {
                    $char = substr($desc, $max_length, 1);
                    $max_length--;
                }
                //echo $max_length;
                $desc = substr($desc, 0, $max_length+1);
                $desc = preg_replace('/\W$/', '', $desc).'...';
            }
        }

        return $desc;
    }




    public function convertMonth($month)
    {
        $m = 0;
        switch ($month) {
        case 'jan':
            $m = 1;
            break;
        case 'feb':
            $m = 2;
            break;
        case 'mar':
            $m = 3;
            break;
        case 'apr':
            $m = 4;
            break;
        case 'may':
            $m = 5;
            break;
        case 'jun':
            $m = 6;
            break;
        case 'jul':
            $m = 7;
            break;
        case 'aug':
            $m = 8;
            break;
        case 'sep':
            $m = 9;
            break;
        case 'oct':
            $m = 10;
            break;
        case 'nov':
            $m = 11;
            break;
        case 'dec':
            $m = 12;
            break;
        default:
            break;
        }

        return $m;
    }

}
