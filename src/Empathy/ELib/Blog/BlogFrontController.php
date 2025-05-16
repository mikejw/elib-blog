<?php

namespace Empathy\ELib\Blog;

use Empathy\ELib\Model,
    Empathy\MVC\Controller\CustomController;

define('DESC', 'Web programmer guy');

class BlogFrontController extends CustomController
{

    public function default_event()
    {
        $b = Model::load('BlogItem');
        $bt = Model::load('BlogTag');
        $blogs = array();

        $sql = '';
        $params = [];

        if (isset($_GET['active_tags'])) {
            $active_tags = $_GET['active_tags'];

            $this->presenter->assign('active_tags', $active_tags);
            $this->presenter->assign('active_tags_string', implode('+', $active_tags));

            $t = Model::load('TagItem');
            //$active_tags = $t->getIds(explode('+', $_GET['active_tags']), true);
            $tags = $t->getIds($active_tags, true);
            $blogs = $b->buildUnionString($bt->getBlogs($tags));

            if ($blogs == '(0,)' || sizeof($tags) != sizeof($active_tags)) {
                $this->presenter->assign('module', '');
                //$this->error('Intersection of tags produced no results.', true);
                $this->http_error(404);
            } else {
                $sql = ' WHERE t1.id IN?';
                $params[] = $blogs;
            }
        } else {
            $this->presenter->assign('active_tags', array());
        }

        if ($sql != '') {
            $sql .= ' AND';
        } else {
            $sql .= ' WHERE';
        }
        $sql .= ' t1.user_id = t2.id';
        $sql .= ' AND t1.status = 2';
        $sql .= ' ORDER BY t1.stamp DESC';

        $blogs = $b->getAllCustomPaginateSimpleJoin(
            '*,UNIX_TIMESTAMP(stamp) AS stamp, t1.id AS blog_id',
            Model::getTable('UserItem'),
            $sql,
            1,
            12,
            $params
        );
        //$this->presenter->assign('blogs', $blogs);

        // truncate
        if(defined('ELIB_TRUNCATE_BLOG_ITEMS') &&
           ELIB_TRUNCATE_BLOG_ITEMS == true)
        {
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
        }

        // get images
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
        //    exit();

        $this->presenter->assign('blogs', $blogs);
        //$this->presenter->assign('blog_images', $images);

        // get tags
        $t = Model::load('TagItem');
        $tags = $t->getAllTags();

        foreach ($tags as $index => $item) {
            $tags[$index]['tag_esc_1'] = '/\+'.$tags[$index]['tag'].'/';
            $tags[$index]['tag_esc_2'] = '/'.$tags[$index]['tag'].'\+/';
        }
        $this->presenter->assign('tags', $tags);

        $this->setTemplate('elib://blog.tpl');

        $this->assign('archive', $b->getArchive());

    }

    public function feed()
    {
        header("Content-type: text/xml");
        //$title = TITLE.' RSS Feed';
        $title = TITLE;
        $link = 'http://'.WEB_ROOT.PUBLIC_DIR;
        $description = DESC;
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

    public function default_event_old()
    {
        $b = Model::load('BlogItem');
        $bt = Model::load('BlogTag');
        $blogs = [];
        $sql = '';
        $params = [];

        if(isset($_GET['active_tags'])) {
            // if (sizeof($_SESSION['active_tag']) > 0) 
            $t = Model::load('TagItem');
            //$active_tags = $t->getIds(explode('+', $_GET['active_tags']), true);
            $tags = $t->getIds($_GET['active_tags'], true);
            $blogs = $b->buildUnionString($bt->getBlogs($tags));

            if ($blogs == '(0,)') {
                $this->presenter->assign('module', '');
                $this->error('Intersection of tags produced no results.', true);
            } else {
                $sql .= ' WHERE id IN? ORDER BY ID DESC';
                $params[] = $blogs;
            }
        } else {
            $sql .= ' ORDER BY ID DESC';
            $this->presenter->assign('active_tags', []);
        }

        $blogs = $b->getAllCustomPaginate($sql, 1, 200, $params);
        $this->presenter->assign('blogs', $blogs);

        // get tags
        $t = Model::load('TagItem');
        $tags = $t->getAll();
        foreach ($tags as $index => $item) {
            $tags[$index]['tag_esc'] = '+'.$tags[$index]['tag'];
        }

        $this->presenter->assign('tags', $tags);
    }

    public function tags()
    {
        /*
          $_GET['active_tags'] = $this->getTags();
          if (sizeof($_GET['active_tags']) < 1) {
          $_SESSION['active_tags'] = array();
          } else {
          $_SESSION['active_tags'] = $_GET['active_tags'];
          }
          $this->redirect('');
        */

        if (!isset($_GET['active_tags'])) {
            $this->redirect('');
        }
        $_GET['active_tags'] = $this->getTags();

        $title = 'Items tagged ';
        $i = 0;
        foreach ($_GET['active_tags'] as $tag) {
            $title .= '"'.$tag.'" ';
            if ($i+1 != sizeof($_GET['active_tags'])) {
                $title .= 'and ';
            }
            $i++;
        }
        $title .= 'in Mike Whiting\'s Blog';
        $this->presenter->assign('custom_title', $title);

        $this->presenter->assign('active_tags', $_GET['active_tags']);
        $this->presenter->assign('active_tags_string', implode('+', $_GET['active_tags']));
        $this->default_event();
    }

    public function getTags()
    {
        /*
          $fullURI = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
          $removeLength = strlen(WEB_ROOT.PUBLIC_DIR);
          $uriString = substr($fullURI, $removeLength + 1);
          $uri_arr = explode('/', $uriString);

          $index = sizeof($uri_arr) - 1;

          if (sizeof($uri_arr) < 3 || $uri_arr[$index] == '') {
          return array();
          } else {
          $tags = $uri_arr[$index];

          return explode('+', $tags);
          }
        */

        return explode('+', urlencode($_GET['active_tags']));
    }

    public function item()
    {
        /* comments not allowed
           if (isset($_POST['submit'])) {
           $bc = new BlogComment($this);
           $bc->blog_id = $_GET['id'];
           $bc->status = 1;
           $bc->body = $_POST['body'];
           $bc->heading = '';
           $bc->user_id = $_SESSION['user_id'];
           $bc->validates();

           if ($bc->val->hasErrors()) {
           $this->presenter->assign('comment', $bc);
           $this->presenter->assign('errors', $bc->val->errors);
           } else {
           $bc->stamp = date('Y-m-d H:i:s', time());
           $bc->insert(['body']);
           $this->redirect('news/item/'.$bc->blog_id);
           }

           }
        */

        if (isset($_GET['id']) && $_GET['id'] == 0) {
            $b = Model::load('BlogItem');
            $_GET['id'] = $b->findByArchiveURL($this->convertMonth($_GET['month']), $_GET['year'], $_GET['day'], $_GET['slug']);
        }

        if (!$this->initID('id', -1, true)) {
            $this->http_error(400);
        }
        $b = Model::load('BlogItem');
        if (!$b->load($_GET['id'])) {
            $this->http_error(404);
        }
        $b->body = preg_replace('/mid_/', 'tn_', $b->body);

        $u = Model::load('UserItem');
        $u->load($b->user_id);

        /*
          $bi = new BlogImage($this);
          $blog_images = $bi->getForIDs(array($b->id));

          if (sizeof($blog_images) > 0) {
          $images = $blog_images[$b->id];
          } else {
          $images = array();
          }
        */

        $bc = Model::load('BlogComment');

        $params = [];
        $sql = ' WHERE t1.user_id = t2.id';
        $sql .= ' AND t1.status = 1';
        $sql .= ' AND t1.blog_id = ?';
        $params[] = $b->id;
        $sql .= ' ORDER BY t1.stamp';
        $comments = $bc->getAllCustomPaginateSimpleJoin(
            '*,t1.id AS id',
            Model::getTable('UserItem'),
            $sql,
            1,
            200,
            $params
        );

        $this->presenter->assign('comments', $comments);

        //$this->presenter->assign('images', $images);

        $this->presenter->assign('author', $u->username);
        $this->presenter->assign('blog', $b);
        $this->presenter->assign('custom_title', $b->heading.' - Mike Whiting\'s Blog');

        $this->setTemplate('elib://blog_item.tpl');
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

    public function year()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $b = Model::load('BlogItem');
            $months = $b->getYear($_GET['id']);
            $this->presenter->assign('months', $months);
            $this->presenter->assign('year', $_GET['id']);
            $this->presenter->assign('custom_title', "Archive for ".$_GET['id']." - Mike Whiting's Blog");
        }
        $this->setTemplate('elib://blog_year.tpl');
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
        $this->setTemplate('elib://blog_month.tpl');
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
        $this->setTemplate('elib://blog_day.tpl');
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
