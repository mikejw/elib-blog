<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model,
    Empathy\MVC\Entity;

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
            $sql = 'SELECT * FROM '.Model::getTable('BlogImage').' WHERE blog_id = '.$item
                .' ORDER BY id';
            $error = 'Could not get blog images.';
            $result = $this->query($sql, $error);
            if ($result->rowCount() > 0) {
                foreach ($result as $row) {
                    $images[$item][] = $row;
                }
            }
        }

        return $images;
    }

}
