<?php

namespace Empathy\ELib\Blog;
use Empathy\MVC\Config;
use Empathy\MVC\DI;

class Util
{
    private $firstImage = '';


    public function setFirstImage($image)
    {
        if ($this->firstImage === '') {
            $this->firstImage = 'mid_' . $image;
        }
    }

    public function getFirstImage()
    {
        return $this->firstImage;
    }


    /**
     * @param $input string
     * @return string
     * Based on "modifier.blog_images.php" (mikejw/elibs)
     */
    public function parseBlogImages($input)
    {
        $proto = '';
        try {
            $sslPlugin = DI::getContainer()->get('SmartySSL');
            if ($sslPlugin->isSecure()) {
                $proto = 'https';
            }
        } catch (\Exception $e) {
            $proto = 'http';
        }


        $web_root = Config::get('WEB_ROOT');
        $public_dir = Config::get('PUBLIC_DIR');
        $self = $this;
        return preg_replace_callback('/\[blog-image\:([A-Za-z0-9=]*)\]/',
            function($matches) use ($web_root, $public_dir, $proto, $self) {
                $data = json_decode(base64_decode($matches[1]));

                $self->setFirstImage($data->filename);

                return '<img class="'
                    . $data->centered
                    . ' '
                    . $data->fluid
                    . "\" src=\"$proto://"
                    . $web_root
                    . $public_dir
                    . '/uploads/'
                    . $data->size
                    . $data->filename
                    . '" alt="' . $data->caption . '"'
                    . ' data-payload="'
                    . $matches[1]
                    . '" data-title="' . $data->caption . '"'
                    . ' />';
            },
            $input
        );
    }
}