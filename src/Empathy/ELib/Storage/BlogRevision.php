<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\Entity;

class BlogRevision extends Entity
{
    const TABLE = 'blog_revision';

    public $id;
    public $blog_id;
    public $body;

    
    public function validates()
    {
        if ($this->body == '') {
            $this->addValError('Invalid body');
        }
    }

    public function loadSaved($blog)
    {;
        $sql = 'select max(id) as max from '.self::TABLE
            .' where blog_id = ?'
            .' group by blog_id';
        
        $result = $this->query($sql, 'Could not fetch latest blog reivision', array($blog->id));
        $rows = $result->fetch();

        if (is_array($rows) && count($rows) === 1) {
            $this->id = $rows['max'];
            $this->load();
            $blog->body = $this->body;
        }
        return $blog;
    }
}
