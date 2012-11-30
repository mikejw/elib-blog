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

    public function getItems($found_items, $limit)
    {
        $blogs = array();
        $sql = 'SELECT t1.heading, t1.body,COUNT(t3.id) AS comments,UNIX_TIMESTAMP(t1.stamp) AS stamp, t1.id AS blog_id'
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
            .' MONTHNAME(stamp) AS monthname, heading FROM '.Model::getTable('BlogItem')
            .' WHERE status = 2 ORDER BY stamp DESC';
        $error = 'Could not get blog archive.';
        $result = $this->query($sql, $error);

        foreach ($result as $row) {
            $year = $row['year'];
            $month = $row['monthname'];
            $id = $row['id'];
            $archive[$year][$month][$id] = ucwords($row['heading']);
        }

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
}
