<?php

namespace Empathy\ELib\Storage;
use Empathy\MVC\Entity as Entity;

class BlogAttachment extends Entity
{
    public $id;
    public $blog_id;
    public $filename;
    public $url;

    const TABLE = 'blog_attachment';



}
