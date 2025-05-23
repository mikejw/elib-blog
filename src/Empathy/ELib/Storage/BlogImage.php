<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\BlogImage as EBlogImage;

class BlogImage extends Entity
{
    const TABLE = 'blog_image';

    public $id;
    public $blog_id;
    public $filename;
    public $image_width;
    public $image_height;

    public function getForIDs($ids)
    {
        $images = array();
        foreach ($ids as $item) {
            $sql = 'SELECT * FROM '.Model::getTable(EBlogImage::class).' WHERE blog_id = ?'
                .' ORDER BY id';
            $error = 'Could not get blog images.';
            $result = $this->query($sql, $error, array($item));
            if ($result->rowCount() > 0) {
                foreach ($result as $row) {
                    $images[$item][] = $row;
                }
            }
        }
        return $images;
    }
}
