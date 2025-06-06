<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\BlogItemCategory;
use Empathy\ELib\Storage\BlogItem as EBlogItem;
use Empathy\ELib\Storage\BlogAttachment;
use Empathy\ELib\Storage\UserItem;
use Empathy\ELib\Storage\BlogComment;

class BlogItem extends Entity
{
    const TABLE = 'blog';
    const DEF_BLOG_PER_PAGE = 2;

    public $id;
    public $status;
    public $user_id;
    public $stamp;
    public $heading;
    public $body;
    public $slug;
    private $pages;
    private $category;
    private $tags = [];
    private $cats = [];

    private function getCategoryBlogs($cat)
    {
        $cat_blogs_string = ['', []];
        if($cat !== null && $cat != 0) {
            $ids = array(0);
            $sql = 'SELECT blog_id from '.Model::getTable(BlogItemCategory::class).' where blog_category_id = ?';
            $error = 'Could not get blogs from cateogry.';
            $result = $this->query($sql, $error, array($cat));
            if($result->rowCount() > 0) {
                foreach($result as $row) {
                    $ids[] = $row['blog_id'];
                }
            }
            $cat_blogs_string = $this->buildUnionString($ids);
        }
        return $cat_blogs_string;
    }


    private function getItemsQuery($found_items, $cat_blogs_string, $limit=array(), $authorId = null)
    {
        $queryParams = [];
        $sql = 'SELECT t1.heading, t1.body,COUNT(t5.id) AS comments,'
            .' UNIX_TIMESTAMP(t1.stamp) AS stamp, t1.id AS blog_id, t1.slug, any_value(t4.body_revision) as body_revision';
        $sql .= ' FROM '.Model::getTable(UserItem::class).' t2,'
            .Model::getTable(EBlogItem::class).' t1';

        $sql .= ' left join'
            .' (select max(id) as max, any_value(blog_id) as blog_id from blog_revision group by blog_id) t3'
            .' on t3.blog_id = t1.id'
            .' left join (select id, body as body_revision from blog_revision) t4 on t3.max = t4.id';

        $sql .= ' LEFT JOIN '.Model::getTable(BlogComment::class).' t5'
            .' ON t1.id = t5.blog_id'
            .' WHERE t1.user_id = t2.id';

        if (count($found_items[1])) {
            $sql .= ' AND t1.id IN ' . $found_items[0];
            $queryParams = array_merge($queryParams, $found_items[1]);
        }
        $sql .= ' AND t1.status = ?';
        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (count($cat_blogs_string[1])) {
            $sql .= ' AND t1.id IN ' . $cat_blogs_string[0];
            $queryParams = array_merge($queryParams, $cat_blogs_string[1]);
        }

        if (!is_null($authorId)) {
            $sql .= ' AND t1.user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= ' GROUP BY t1.id'
            .' ORDER BY t1.stamp DESC, t1.id DESC';

        if (sizeof($limit)) {
            $sql .= ' limit '. $limit[0] . ', ' . $limit[1];
        }
        return array($sql, $queryParams);
    }

    public function getItems($found_items, $cat=null, $page=1, $authorId = null, $count = self::DEF_BLOG_PER_PAGE)
    {       
        $cat_blogs_string = $this->getCategoryBlogs($cat);

        $blogs = array();
        $error = 'Could not get blog items.';
        list($sql, $params) = $this->getItemsQuery($found_items, $cat_blogs_string, array(), $authorId);
        $result = $this->query($sql, $error, $params);
        $rows = $result->rowCount();

        $per_page = $count;
        $pages = ceil($rows / $per_page);
        
        $start = ($page * $per_page) - $per_page;
        $end = $per_page;
        
        //echo "start: $start end: $end<br />";

        list($sql, $params) = $this->getItemsQuery($found_items, $cat_blogs_string, array($start, $end), $authorId);
        $result = $this->query($sql, $error, $params);

        $struct_pages = array();
        for ($i = 0; $i < $pages; $i++) {
            $item = array();
            if ($page != $i+1) {
                $item['current'] = false;
            } else {
                $item['current'] = true;
            }
            $struct_pages[$i+1] = $item;
        }
        $this->pages = $struct_pages;

        foreach ($result as $row) {

            if (isset($row['body_revision'])) {
                $row['body'] = $row['body_revision'];
            }
            $blogs[] = $row;
        }

        Model::disconnect(array($this));
        return array($this, $blogs);
    }


    public function getPages()
    {
        return $this->pages;
    }


    public function validates()
    {
	    $allowed_chars = '/[\.\s\\\\\/]/';
        if ($this->heading == '' || !ctype_alnum(preg_replace($allowed_chars, '', $this->heading))) {
            $this->addValError('Invalid heading', 'heading');
        }
        if ($this->body == '') {
            $this->addValError('Invalid body', 'body');
        }
        if ($this->slug != '') {
            if (!ctype_alnum(str_replace('-', '', $this->slug))) {
                $this->addValError('Invalid URL Slug', 'slug');
            }
        }
        if (!count($this->category)) {
            $this->addValError('Choose at least one category', 'category');
        }
    }

    public function getFeed($authorId = null)
    {
        $queryParams = array();
        $entry = array();
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable(EBlogItem::class)
            .' WHERE status = ?';

        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (!is_null($authorId)) {
            $sql .= ' AND user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= ' ORDER BY stamp DESC, id DESC LIMIT 0, 5';
        $error = 'Could not get blog feed.';
        $result = $this->query($sql, $error, $queryParams);
        $i = 0;
        foreach ($result as $row) {
            $entry[$i] = $row;
            $i++;
        }

        return $entry;
    }

    public function checkForDuplicates($input)
    {
        $temp = '';
        $error = 0;
        foreach ($input as $item) {
            $temp = array_pop($input);
            if (in_array($temp, $input)) {
                $error = 1;
            }
            array_push($input, $temp);
        }
        if ($error) {
            $this->addValError('Duplicate tags submitted');
        }
    }

    public function buildTags($tags)
    {
        $built = [];
        if ($tags != '') {
            if (ctype_alnum(str_replace(',', '', str_replace(' ', '', $tags)))) {
                $built = explode(',', str_replace(' ', '', $tags));
            } else {
                $this->addValError('Invalid tags submitted');
            }
        }
        return $built;
    }

    public function getStamp()
    {
        $stamp = 0;
        $sql = 'SELECT UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable(EBlogItem::class)
            .' WHERE id = ?';
        $error = 'Could not get stamp.';
        $result = $this->query($sql, $error, array($this->id));
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $stamp = $row['stamp'];
        }

        return $stamp;
    }

    public function getRecentlyModified()
    {
        $stamp = 0;
        $sql = 'SELECT UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable(EBlogItem::class)
            .' ORDER BY stamp DESC LIMIT 0, 1';
        $error = 'Could not get recently modified blogs';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $stamp = $row['stamp'];
        }

        return $stamp;
    }

    public function getAllForSiteMap()
    {
        $blogs = array();
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable(EBlogItem::class).' b'
            .' WHERE status = ?';
        $error = 'Could not get blogs for sitemap';
        $result = $this->query($sql, $error, array(BlogItemStatus::PUBLISHED));
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                //	    $row['slug'] = $this->urlSlug($row['name']);
                array_push($blogs, $row);
            }
        }

        return $blogs;
    }

    public function getArchive($cat = null, $authorId = null)
    {
        $queryParams = [];
        $cat_blogs_string = $this->getCategoryBlogs($cat);

        $archive = array();
        $sql = 'SELECT id, YEAR(stamp) AS year, MONTH(stamp) AS month,'
            .' MONTHNAME(stamp) AS monthname,'
            .' DAY(stamp) AS day,'
            .' slug,'
            .' heading FROM '.Model::getTable(EBlogItem::class)
            .' WHERE status = ?';

        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (count($cat_blogs_string[1])) {
            $sql .= ' AND id IN ' . $cat_blogs_string[0];
            $queryParams = array_merge($queryParams, $cat_blogs_string[1]);
        }

        if (!is_null($authorId)) {
            $sql .= ' AND user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= ' ORDER BY stamp DESC, id DESC';

        $error = 'Could not get blog archive.';
        $result = $this->query($sql, $error, $queryParams);

        foreach ($result as $row) {
            $year = $row['year'];
            $month = $row['monthname'];
            $id = $row['id'];
            //$archive[$year][$month][$id] = ucwords($row['heading']);
            $archive[$year][$month][$id]['heading'] = ucwords($row['heading']);
            $archive[$year][$month][$id]['day'] = str_pad($row['day'], 2, '0',STR_PAD_LEFT);
            $archive[$year][$month][$id]['slug'] = $row['slug'];
            $archive[$year][$month][$id]['month_slug'] = strtolower(substr($month, 0, 3));
        }
        return $archive;
    }

    public function getYear($year, $authorId = null)
    {
        $queryParams = array();
        $start = mktime(0, 0, 0, 1, 1, $year);
        $finish = mktime(0, 0, -1, 1, 1, $year+1);

        $blogs = array();
        $sql = 'SELECT *,UNIX_TIMESTAMP(stamp) AS stamp'
            .' FROM '.self::TABLE
            .' WHERE UNIX_TIMESTAMP(stamp) >= '.$start
            .' AND UNIX_TIMESTAMP(stamp) <= '.$finish
            .' AND status = ?';

        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (!is_null($authorId)) {
            $sql .= ' AND user_id = ?';
            array_push($queryParams, $authorId);
        }


        $sql .= ' ORDER BY stamp';
        $error = 'Could not get blogs for the year';
        $result = $this->query($sql, $error, $queryParams);

        $months = array();
        $slug = '';

        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $month = date("F", $row['stamp']);
                $slug = strtolower(substr($month, 0, 3));
                $months[$slug]['month'] = $month;
                if (!isset($months[$slug]['count'])) {
                    $months[$slug]['count'] = 0;
                }
                $months[$slug]['count']++;
            }
        }

        return $months;
    }

    public function getMonth($month, $year, $authorId = null)
    {
        $queryParams = array();
        $finish_month = $month;
        $finish_year = $year;
        if ($month == 12) {
            $finish_month = 0;
            $finish_year = $year + 1;
        }

        $start = mktime(0, 0, 0, $month, 1, $year);
        $finish = mktime(0, 0, -1, $finish_month+1, 1, $finish_year);

        $blogs = array();
        $sql = 'SELECT *,UNIX_TIMESTAMP(stamp) AS stamp'
            .' FROM '.self::TABLE
            .' WHERE UNIX_TIMESTAMP(stamp) >= '.$start
            .' AND UNIX_TIMESTAMP(stamp) <= '.$finish
            .' AND status = ?';

        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (!is_null($authorId)) {
            $sql .= ' AND user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= ' ORDER BY stamp';
        $error = 'Could not get blogs for the month';
        $result = $this->query($sql, $error, $queryParams);

        $blogs = array();

        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $row['day'] = date("d", $row['stamp']);
                $row['day_str'] = preg_replace('/^0+/', '', $row['day']);
                $row['suffix'] = date("S", $row['stamp']);
                array_push($blogs, $row);
            }
        }

        return $blogs;
    }

    public function getDay($month, $year, $day, $authorId = null)
    {
        $queryParams = array();
        $finish_day = $day;
        $finish_month = $month;
        $finish_year = $year;
        if ($month == 12 && $day = 31) {
            $finish_month = 1;
            $finish_year = $year + 1;
            $finish_day = 0;
        }

        $start = mktime(0, 0, 0, $month, $day, $year);
        $finish = mktime(0, 0, -1, $finish_month, $finish_day+1, $finish_year);

        $blogs = array();
        $sql = 'SELECT *,UNIX_TIMESTAMP(stamp) AS stamp'
            .' FROM '.self::TABLE
            .' WHERE UNIX_TIMESTAMP(stamp) >= '.$start
            .' AND UNIX_TIMESTAMP(stamp) <= '.$finish
            .' AND status = ?';

        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (!is_null($authorId)) {
            $sql .= ' AND user_id = ?';
            array_push($queryParams, $authorId);
        }

        $sql .= ' ORDER BY stamp';
        $error = 'Could not get blogs for the month';
        $result = $this->query($sql, $error, $queryParams);

        $blogs = array();

        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $row['day'] = date("jS", $row['stamp']);
                array_push($blogs, $row);
            }
        }
        
        return $blogs;
    }

    public function getMonthName($m, $y)
    {
        $start = mktime(0, 0, 0, $m, 1, $y);

        return date('F', $start);
    }

    public function findByArchiveURL($month, $year, $day, $slug, $authorId = null)
    {
        $queryParams = array();
        $finish_day = $day;
        $finish_month = $month;
        $finish_year = $year;
        if ($month == 12 && $day == 31) {
            $finish_month = 1;
            $finish_year = $year + 1;
            $finish_day = 0;
        }

        $start = mktime(0, 0, 0, $month, $day, $year);
        $finish = mktime(0, 0, -1, $finish_month, $finish_day+1, $finish_year);

        $blogs = array();
        $sql = 'SELECT id'
            .' FROM '.self::TABLE
            .' WHERE UNIX_TIMESTAMP(stamp) >= '.$start
            .' AND UNIX_TIMESTAMP(stamp) <= '.$finish
            .' AND slug = ?'
            .' AND status = ?';

        array_push($queryParams, $slug);
        array_push($queryParams, BlogItemStatus::PUBLISHED);

        if (!is_null($authorId)) {
            $sql .= ' AND user_id = ?';
            array_push($queryParams, $authorId);
        }

        $error = 'Could not get blog id by archive url';
        $result = $this->query($sql, $error, $queryParams);

        $id = 0;
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $id = $row['id'];
        }
        
        return $id;
    }
    
    public function getPodcast()
    {
        $podcast = array();
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.self::TABLE.' b,'
            .' '.Model::getTable(BlogAttachment::class).' a'
            .' WHERE a.blog_id = b.id AND b.status = ?'
            .' ORDER BY b.stamp DESC';
        $error = 'Could not get podcast.';
        $result = $this->query($sql, $error, array(BlogItemStatus::PUBLISHED));
        foreach($result as $row)
        {
            $podcast[] = $row;
        }
        return $podcast;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setTags($tags = []) {
        $this->tags = $tags;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setCats($cats = []) {
        $this->cats = $cats;
    }

    public function getCats()
    {
        return $this->cats;
    }
}
