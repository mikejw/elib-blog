<?php


namespace Empathy\ELib\Blog;


class FeedController
{

   public function feed_burner()
    {
        include('http://feeds.feedburner.com/HoorayForSex');
        return false;
    }

    public function feed()
    {
        header('Content-type: application/xml');
        $title = 'Hooray For Sex';
        $link = 'http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR');
        $description = DESC;
        $language = 'en-us';
        $category = 'Music';
        $image = 'http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR').'/img/hfs_new.jpg';
        $explicit = "Yes";

        $feedArray =
            array(
                'title' => $title,
                'link' => 'http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR'),
                'description' => strip_tags($description),

                'itunes' => array(
                    'summary' => $description,                        
                    'author' => 'HFS Podcast',                
                    'category' => array(array('main' => $category)),                 
                    'image' => $image,
                    'explicit' => $explicit               
                    ),
                  
                'language' => $language,
                'charset' => 'utf-8',
                'pubDate' => time(),
                'entries' => array(),
                );
    
        $b = Model::load('BlogItem');
        $blogs = $b->getPodcast();
    
        foreach($blogs as $article)
        {
            $feedArray['entries'][] =
                array(
                    'title' => $article['heading'],
                    'link' => 'http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR'),
                    'guid' => $article['blog_id'],
                    'description' => strip_tags($article['body']),  
                    'lastUpdate' => $article['stamp'],
                    'enclosure' => array(
                        array('url' => 'http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR').'/episodes/'.$article['filename'])
                        )
                    );
        }
 
        //$feed = \Zend_Feed::importArray($feedArray, 'rss');
        $feed = \Zend_Feed::importBuilder(new \Zend_Feed_Builder($feedArray), 'rss');
        $feed->send();
        return false;
    }

}


