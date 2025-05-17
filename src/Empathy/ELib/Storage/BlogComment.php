<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

class BlogComment extends Entity
{
    const TABLE = 'blog_comment';

    public $id;
    public $blog_id;
    public $user_id;
    public $status;
    public $stamp;
    public $heading;
    public $body;

    public function validates()
    {
        $allowed_chars = '/[\.\s\\\\\/]/';
        if ($this->heading == '' || !ctype_alnum(preg_replace($allowed_chars, '', $this->heading))) {
            $this->addValError('Invalid heading');
        }
        if ($this->body == '') {
            $this->addValError('Invalid body');
        }
    }

}
