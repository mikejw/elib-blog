<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;

class BlogRevision extends Entity
{
    const TABLE = 'blog_revision';

    public $id;
    public $blog_id;
    public $body;
    public $meta;
    public $stamp;

    
    public function validates()
    {
        if ($this->body == '') {
            $this->addValError('Invalid body');
        }
    }

    public function loadSaved($blog, $revision = 0)
    {
        $meta = array();
        if ($revision > 0) {
            if ($this->load($revision) && $blog->id === $this->blog_id) {
                $meta = json_decode($this->meta, true);
                if ($meta) {
                    $blog->heading = $meta['heading'];
                    $blog->slug = $meta['slug'];
                }
                $blog->body = $this->body;
            } else {
                throw new \Exception('Revision not found');
            }

        } else {
            $sql = 'select max(id) as max from '.self::TABLE
                .' where blog_id = ?'
                .' group by blog_id';

            $result = $this->query($sql, 'Could not fetch latest blog revision', array($blog->id));
            $rows = $result->fetch();

            if (is_array($rows) && count($rows) === 1) {
                $this->load($rows['max']);
                $blog->body = $this->body;
            }
        }
       
        return array($blog, $meta);
    }

    public function loadAll($blog)
    {
      $revisions = [];
      $sql = 'select *, UNIX_TIMESTAMP(stamp) as stamp from '.self::TABLE
            .' where blog_id = ?'
            .' order by stamp desc';

        $result = $this->query($sql, 'Could not fetch latest blog revision', array($blog->id));
        $rows = $result->fetchAll();

        if (is_array($rows) && count($rows) > 0) {
            $i = 0;
            foreach ($rows as &$r) {
                $revisions[$r['id']] = count($rows) - ($i) .' - '.date('M jS, Y \a\t g:ia', $r['stamp']);
                $i++;
            }
        }
        
        return $revisions;
    }
}
