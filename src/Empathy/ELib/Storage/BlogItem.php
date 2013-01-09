<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\Entity;

class BlogItem extends Entity
{
    const TABLE = 'blog';

    public $id;
    public $status;
    public $user_id;
    public $stamp;
    public $heading;
    public $body;
    public $slug;

    public function getItems($found_items, $limit, $cat=null)
    {
        if($cat !== null) {
            
        }

        $limit = 10000;
        $blogs = array();
        $sql = 'SELECT t1.heading, t1.body,COUNT(t3.id) AS comments,UNIX_TIMESTAMP(t1.stamp) AS stamp, t1.id AS blog_id, t1.slug'
            .' FROM '.Model::getTable('BlogItem').' t1'
            .' LEFT JOIN '.Model::getTable('BlogComment').' t3'
            .' ON t1.id = t3.blog_id'

            .', '.Model::getTable('UserItem').' t2'
            .' WHERE';
        if ($found_items != '(0,)') {
            $sql .= ' t1.id IN'.$found_items.' AND';
        }
        $sql .= ' t1.user_id = t2.id'
            .' AND t1.status = 2'
            .' GROUP BY t1.id'
            .' ORDER BY t1.stamp DESC'
            .' LIMIT 0, '.$limit;
        $error = 'Could not get blog items.';
        $result = $this->query($sql, $error);

        foreach ($result as $row) {
            $blogs[] = $row;
        }

        return $blogs;
    }

    public function validates()
    {
        if ($this->heading == '' || !ctype_alnum(str_replace(' ', '', $this->heading))) {
            $this->addValError('Invalid heading');
        }
        if ($this->body == '') {
            $this->addValError('Invalid body');
        }
        if ($this->slug != '') {
            if (!ctype_alnum(str_replace('-', '', $this->slug))) {
                $this->addValError('Invalid URL Slug');
            }
        }
    }

    public function getFeed()
    {
        $entry = array();
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable('BlogItem')
            .' WHERE status = '.BlogItemStatus::PUBLISHED.' ORDER BY stamp DESC LIMIT 0, 5';
        $error = 'Could not get blog feed.';
        $result = $this->query($sql, $error);
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

    public function buildTags()
    {
        $tags = array();
        if ($_POST['tags'] != '') {
            if (ctype_alnum(str_replace(',', '', str_replace(' ', '', $_POST['tags'])))) {
                $tags = explode(',', str_replace(' ', '', $_POST['tags']));
            } else {
                $this->addValError('Invalid tags submitted');
            }
        }

        return $tags;
    }

    public function getStamp()
    {
        $stamp = 0;
        $sql = 'SELECT UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable('BlogItem')
            .' WHERE id = '.$this->id;
        $error = 'Could not get stamp.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $stamp = $row['stamp'];
        }

        return $stamp;
    }

    public function getRecentlyModified()
    {
        $stamp = 0;
        $sql = 'SELECT UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable('BlogItem')
            .' ORDER BY stamp DESC LIMIT 0,1';
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
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable('BlogItem').' b'
            .' WHERE status = 2';
        $error = 'Could not get blogs for sitemap';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                //	    $row['slug'] = $this->urlSlug($row['name']);
                array_push($blogs, $row);
            }
        }

        return $blogs;
    }

    public function getArchive()
    {
        $archive = array();
        /*
          $sql = 'SELECT MAX(UNIX_TIMESTAMP(stamp)) AS max,'
          .' MIN(UNIX_TIMESTAMP(stamp)) AS min'
          .' FROM '.Model::getTable('BlogItem');
        */

        $sql = 'SELECT id, YEAR(stamp) AS year, MONTH(stamp) AS month,'
            .' MONTHNAME(stamp) AS monthname,'
            .' DAY(stamp) AS day,'
            .' slug,'
            .' heading FROM '.Model::getTable('BlogItem')
            .' WHERE status = 2 ORDER BY stamp DESC';
        $error = 'Could not get blog archive.';
        $result = $this->query($sql, $error);

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

//        print_r($archive);

        return $archive;
        //    print_r($archive);

        //    $max = $row['stamp'];

        /*
          $sql = 'SELECT MIN(UNIX_TIMESTAMP(stamp)) AS stamp FROM '.Model::getTable('BlogItem');
          $result = $this->query($sql, $error);
          $row = $result->fetch();
          $max = $row['stamp'];
        */

    }

    public function getYear($year)
    {
        $start = mktime(0, 0, 0, 1, 1, $year);
        $finish = mktime(0, 0, -1, 1, 1, $year+1);

        $blogs = array();
        $sql = 'SELECT *,UNIX_TIMESTAMP(stamp) AS stamp'
            .' FROM '.self::TABLE
            .' WHERE UNIX_TIMESTAMP(stamp) >= '.$start
            .' AND UNIX_TIMESTAMP(stamp) <= '.$finish
            .' AND status = '.BlogItemStatus::PUBLISHED
            .' ORDER BY stamp';
        $error = 'Could not get blogs for the year';
        $result = $this->query($sql, $error);

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

    public function getMonth($month, $year)
    {
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
            .' AND status = '.BlogItemStatus::PUBLISHED
            .' ORDER BY stamp';
        $error = 'Could not get blogs for the month';
        $result = $this->query($sql, $error);

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

    public function getDay($month, $year, $day)
    {
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
            .' AND status = '.BlogItemStatus::PUBLISHED
            .' ORDER BY stamp';
        $error = 'Could not get blogs for the month';
        $result = $this->query($sql, $error);

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

    public function findByArchiveURL($month, $year, $day, $slug)
    {
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
        $sql = 'SELECT id'
            .' FROM '.self::TABLE
            .' WHERE UNIX_TIMESTAMP(stamp) >= '.$start
            .' AND UNIX_TIMESTAMP(stamp) <= '.$finish
            .' AND slug = \''.$slug.'\''
            .' AND status = '.BlogItemStatus::PUBLISHED;
        $error = 'Could not get blog id by archive url';
        $result = $this->query($sql, $error);

        $id = 0;
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $id = $row['id'];
        }
        
        return $id;
    }

}
