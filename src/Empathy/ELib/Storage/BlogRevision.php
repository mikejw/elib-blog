<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\Entity;

class BlogRevisoin extends Entity
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

   
    
}
