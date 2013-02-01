<?php

namespace Empathy\ELib\Blog;

use Empathy\ELib\Model,
    Empathy\ELib\EController,
    Empathy\ELib\User\CurrentUser,
    Empathy\MVC\Session,
    Empathy\MVC\RequestException;

class BlogFrontControllerNew extends EController
{


    public function testFunc()
    {
        return 'plopplop';
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

        $blogs = $this->getBlogs($b, $found_items);

        $this->assign('blogs', $blogs);

        $this->getAvailableTags();
        $this->getArchive($b);
        $this->getCategories();

        $this->assign('current_year', date('Y', time()));
        $this->assign('current_month', date('F', time()));
        if(defined('ELIB_BLOG_MODULE')) {
            $this->assign('blog_module', ELIB_BLOG_MODULE);
        } else {
            $this->assign('blog_module', 'blog');
        }
    }




    public function item()
    {
        if (isset($_POST['submit'])) {
            $this->submitComment();
        }

        if (isset($_GET['id']) && $_GET['id'] == 0) {
            $b = Model::load('BlogItem');
            $_GET['id'] = $b->findByArchiveURL($this->convertMonth($_GET['month']), $_GET['year'], $_GET['day'], $_GET['slug']);
        }

        if (!$this->initID('id', -1, true)) {
            $this->http_error(400);
        }

        $b = Model::load('BlogItem');
        $b->id = $_GET['id'];
        if (!$b->load()) {
            $this->http_error(404);
        }
        $b->body = preg_replace('/mid_/', 'tn_', $b->body);

        $u = Model::load('UserItem');
        $u->id = $b->user_id;
        $u->load();

        $this->getComments($b->id);

        $this->assign('author', $u->username);
        $this->assign('blog', $b);
        $this->assign('custom_title', $b->heading.' - '.TITLE);

        $this->setTemplate('blog_item.tpl');

        $this->getAvailableTags();
        $this->getArchive($b);
        $this->getCategories();
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
                die('not valid date');
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






    public function set_category()
    {        
        $c = Model::load('BlogCategory');        
        Session::set('blog_category', $c->getIdByLabel($_GET['category']));
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
        //$title = TITLE.' RSS Feed';
        $title = TITLE;
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

        echo $xml->asXML();
        exit();
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

    private function getComments($id)
    {
        $bc = Model::load('BlogComment');
        $sql = ' WHERE t1.user_id = t2.id';
        $sql .= ' AND t1.status = 1';
        $sql .= ' AND t1.blog_id = '.$id;
        $sql .= ' ORDER BY t1.stamp';
        $comments = $bc->getAllCustomPaginateSimpleJoin('*,t1.id AS id', Model::getTable('BlogComment'), Model::getTable('UserItem'), $sql, 1, 200);

        $this->assign('comments', $comments);
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
        $title .= 'in '.TITLE;
        $this->assign('custom_title', $title);
    }

    private function getAvailableTags()
    {
        $t = Model::load('TagItem');
        $tags = $t->getAllTags();

        foreach ($tags as $index => $item) {
            $tags[$index]['tag_esc_1'] = '/\+'.$tags[$index]['tag'].'/';
            $tags[$index]['tag_esc_2'] = '/'.$tags[$index]['tag'].'\+/';
            $tags[$index]['share'] = ($tags[$index]['share'] / 10) * 2.5;
        }
        $this->assign('tags', $tags);
    }

    private function getArchive($b)
    {
        $bc = $this->stash->get('blog_category');
        $this->assign('archive', $b->getArchive($bc)); // looks like main query but isn't
    }

    private function getCategories()
    {
        $c = Model::load('BlogCategory');
        $cats = $c->getAllCustom(Model::getTable('BlogCategory'), ' order by id');
        array_push($cats, array('id' => 0, 'label' => 'Any'));
        
        $this->assign('categories', $cats);
    }


    private function getBlogs($b, $found_items)
    {
        $bc = $this->stash->get('blog_category');
        
        $blogs = $b->getItems($found_items, ELIB_BLOG_ENTRIES, $bc);

        $t = Model::load('TagItem');
        foreach ($blogs as &$b_item) {
            $b_item['tags'] = $t->getTagsForBlogItem($b_item['blog_id']);
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
                $blogs[$index]['month_slug'] = strtolower(substr(date("F", $item['stamp']), 0, 3));
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


    private function getActiveTags()
    {
        $t = Model::load('TagItem');
        $bt = Model::load('BlogTag');

        $active_tags = $_GET['active_tags'];
        $active_tags_string = implode('+', $active_tags);

        $tags = $t->getIds($active_tags, true);

        $found_items = $bt->buildUnionString($bt->getBlogs($tags));

        if ($found_items == '(0,)' || sizeof($tags) != sizeof($active_tags)) {

            throw new RequestException('Not found.', RequestException::NOT_FOUND);

        } else {
            $this->setTagsTitle();
        }

        $this->assign('active_tags', $active_tags);
        $this->assign('active_tags_string', $active_tags_string);

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
