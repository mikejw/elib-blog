<?php

namespace Empathy\ELib\Blog;


use Empathy\ELib\AdminController;
use Empathy\ELib\File\Image as ImageUpload;
use Empathy\ELib\File\Upload;
use Empathy\ELib\Model;
use Empathy\MVC\DI;
use Empathy\MVC\RequestException;
use Empathy\MVC\Session;
use Empathy\ELib\Storage\BlogItemStatus;
use Empathy\MVC\Config;


define('REQUESTS_PER_PAGE', 12);


class Controller extends AdminController
{
    public function __construct($boot)
    {
        parent::__construct($boot);

        $vendor = $this->stash->get('vendor');
        if ($vendor && isset($vendor['currentUser']) && $vendor['currentUser']) {
            $this->stash->store('authorId', $vendor['user_id']);
        }
    }

    private function clearCache()
    {
        $cache = $this->stash->get('cache');
        if (is_object($cache)) {
            $cache->clear();
        }
    }

    private function assertAuthorBlog($id)
    {
        $authed = true;
        $authorId = $this->stash->get('authorId');

        $u = DI::getContainer()->get('CurrentUser')->getUser();
        $ua = Model::load('UserAccess');
        if (!($u->auth < $ua->getLevel('admin')) && is_null($authorId)) {
            return;
        }

        if ($authorId) {
            $b = Model::load('BlogItem');
            $sql = 'select id from ' . Model::getTable('BlogItem')
                . ' where user_id = ?'
                . ' and id = ?';
            $result = $b->query($sql, '', array($authorId, $id));
            $authed = $result->rowCount() === 1;
        }
        if (!$authed) {
            throw new RequestException('Denied', RequestException::NOT_AUTHORIZED);
        }
    }

    public function default_event()
    {
        $ui_array = array('page', 'status');
        $this->loadUIVars('ui_blog', $ui_array);
        if (!isset($_GET['page']) || $_GET['page'] == '') {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['status']) || $_GET['status'] == '') {
            $_GET['status'] = 1;
        }
        $this->presenter->assign('page', $_GET['page']);
        $this->presenter->assign('status', $_GET['status']);

        $admin = false;

        // is admin user?
        $u = Model::load('UserItem');
        $u->id = Session::get('user_id');

        $u->load();
        $ua = Model::load('UserAccess');
        if ($u->auth >= $ua->getLevel('admin')) {
            $admin = true;
        }
        $this->presenter->assign('super', $admin);

        $c = Model::load('BlogCategory');
        $cats = $c->getAllCustom(Model::getTable('BlogCategory'), '');
        $cats_arr = array();
        foreach ($cats as $index => $item) {
            $id = $item['id'];
            $cats_arr[$id] = $item['label'];
        }

        $b = Model::load('BlogItem');
        $blogs = array();



        $select = '*,t1.id AS id, t4.body_revision';
        $sql = ' WHERE status = '.$_GET['status'];

        if (!$admin) {
            $sql .= ' AND user_id = '. $this->stash->get('authorId');
        }

        $sql .= ' AND t1.user_id = t2.id';
        $sql .= ' ORDER BY stamp DESC';

        $leftJoins = ' left join'
            .' (select max(id) as max, any_value(blog_id) as blog_id from blog_revision group by blog_id) t3'
            .' on t3.blog_id = t1.id'
            .' left join (select id, body as body_revision from blog_revision) t4 on t3.max = t4.id,';

        $p_nav = $b->getPaginatePagesSimpleJoin(
            $select,
            Model::getTable('BlogItem'),
            Model::getTable('UserItem'),
            $sql,
            $_GET['page'],
            REQUESTS_PER_PAGE,
            $leftJoins
        );
        $this->presenter->assign('p_nav', $p_nav);

        $this->presenter->assign('status', $_GET['status']);

        $blogs = $b->getAllCustomPaginateSimpleJoin(
            $select,
            Model::getTable('BlogItem'),
            Model::getTable('UserItem'),
            $sql,
            $_GET['page'],
            REQUESTS_PER_PAGE,
            $leftJoins
        );
        

        foreach ($blogs as $index => $item) {
            $blog_cats = $c->getCategoriesForBlogItem($blogs[$index]['id']);

            $cats = array();
            foreach ($blog_cats as $bc_id) {
                $cats[] = $cats_arr[$bc_id];
            }
            $blogs[$index]['category'] = implode(', ', $cats);

            if (isset($blogs[$index]['body_revision'])) {
                $blogs[$index]['body'] = $blogs[$index]['body_revision'];
            }
        }


        $this->setTemplate('elib:/admin/blog/blog_admin.tpl');
        $this->presenter->assign('blogs', $blogs);
    }

    private function uploadImage()
    {
        $_GET['id'] = $_POST['id'];
        $sizes = array(array('l_', 800, 600),
                       array('tn_', 200, 200),
                       array('mid_', 468, 5000));
        $u = new ImageUpload('blog', true, $sizes);

        if ($u->error != '') {
            $this->presenter->assign('error', $u->error);
        } else {
            $bi = Model::load('BlogImage');
            $bi->filename = $u->getFile();
            $bi->blog_id = $_GET['id'];
            $bi->image_width = $u->getDimensions()[0];
            $bi->image_height = $u->getDimensions()[1];
            $bi->insert(Model::getTable('BlogImage'), 1, array(), 0);
            $this->redirect('admin/blog/view/'.$_GET['id']);
        }

    /*
          $b = new SectionItem($this);
          $section->getItem($_GET['id']);

          $gallery = strtolower(str_replace(" ", "", $section->label));

          $this->setNavigation();

          if (isset($_POST['upload'])) {
          $upload = new ImageUpload();

          $upload->upload($gallery, true);

          if ($upload->error != "") {
          $this->presenter->assign("error", $upload->error);
          } else {
          $this->redirect("admin/sections/$section->id");
          }
      }
    */
    }

    public function remove_image()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
        $this->assertAuthorBlog($_GET['id']);
        $i = Model::load('BlogImage');
        $i->id = $_GET['id'];
        $i->load();

        $u = new ImageUpload('blog', false, array());
        if ($u->remove(array($i->filename))) {
            $i->delete();
        }

        $this->redirect('admin/blog/view/'.$i->blog_id);
    }

    public function delete()
    {
        $b = Model::load('BlogItem');
        $b->id = $_GET['id'];
        $this->assertAuthorBlog($_GET['id']);
        $b->load();
        $b->status = BlogItemStatus::DELETED;
        $b->save(Model::getTable('BlogItem'), array(), 2);

        Service::removeFromIndex($b);

        $this->redirect('admin/blog/?page=1&status=2');
    }

    public function redraft()
    {
        $b = Model::load('BlogItem');
        $b->id = $_GET['id'];
        $this->assertAuthorBlog($_GET['id']);
        $b->load();
        $b->status = BlogItemStatus::DRAFT;
        $b->save(Model::getTable('BlogItem'), array(), 2);

        $this->clearCache();

        Service::removeFromIndex($b);

        $this->redirect('admin/blog/view/'.$b->id);
    }

    public function publish()
    {
        $b = Model::load('BlogItem');
        $b->id = $_GET['id'];
        $this->assertAuthorBlog($_GET['id']);
        $b->load();
        if (isset($_GET['stamp']) && $_GET['stamp'] == 1) {
            $b->stamp = date('Y-m-d H:i:s', time());
        }
        $b->status = BlogItemStatus::PUBLISHED;
        $b->save(Model::getTable('BlogItem'), array(), 2);

        $this->clearCache();

        Service::addToIndex($b);
                
        $this->redirect('admin/blog/?page=1&status=2');
    }

    public function view()
    {
        $this->assertAuthorBlog($_GET['id']);
        if (isset($_POST['upload_image'])) {
            $this->uploadImage();
        } elseif(isset($_POST['upload_attachment'])) {
            $this->uploadAttachment();
        }   

        $b = Model::load('BlogItem');
        $b->id = $_GET['id'];
        $b->load();
        
        $r = Model::load('BlogRevision');
        $b = $r->loadSaved($b);

        $u = Model::load('UserItem');
        $u->id = $b->user_id;
        $u->load();

        $this->presenter->assign('author', $u->username);
        $this->presenter->assign('blog', $b);

        $bi = Model::load('BlogImage');
        $sql = ' WHERE blog_id = '.$b->id;
        $images = $bi->getAllCustom(Model::getTable('BlogImage'), $sql);

        /*
          $image = array();
          foreach ($images as $item) {
          array_push($image, $item['filename']);
          }
        */

        $this->presenter->assign('images', $images);

        $ba = Model::load('BlogAttachment');
        $sql = ' WHERE blog_id = '.$b->id;    
        $attachments = $ba->getAllCustom(Model::getTable('BlogAttachment'), $sql);

        /*
          $image = array();
          foreach($images as $item)
          {
          array_push($image, $item['filename']);
          }
        */
        $this->presenter->assign('attachments', $attachments);


        // get tags
        $bt = Model::load('BlogTag');
        $tags_arr = $bt->getTags($b->id);
        $tags = implode(', ', $tags_arr);
        $this->presenter->assign('blog_tags', $tags);

        $this->setTemplate('elib:/admin/blog/view_blog_item.tpl');
    }

    public function create()
    {
        $c = Model::load('BlogCategory');
        $cats = $c->getAllCustom(Model::getTable('BlogCategory'), '');
        $cats_arr = array();
        foreach ($cats as $index => $item) {
            $id = $item['id'];
            $cats_arr[$id] = $item['label'];
        }

        $this->presenter->assign('cats', $cats_arr);

        $this->setTemplate('elib:/admin/blog/create_blog.tpl');

        if (isset($_POST['cancel'])) {
            $this->redirect('admin/blog');
        } elseif (isset($_POST['save'])) {
            $b = Model::load('BlogItem');
            $tags_arr = $b->buildTags(); // errors ?

            $b->heading = $_POST['heading'];
            $b->body = $_POST['body'];
            $b->status = BlogItemStatus::DRAFT;
            $b->slug = $_POST['slug'];

            $b->checkForDuplicates($tags_arr);
            $b->validates();

            if ($b->hasValErrors()) {
                $this->presenter->assign('blog', $b);
                $this->presenter->assign('blog_tags', $_POST['tags']);
                $this->presenter->assign('errors', $b->getValErrors());
                $this->assign('blog_cats', $_POST['category']);
            } else {
                $b->assignFromPost(array('user_id', 'id', 'stamp', 'tags', 'status'));
                $b->user_id = Session::get('user_id');
                $b->stamp = date('Y-m-d H:i:s', time());              
                $b->id = $b->insert(Model::getTable('BlogItem'), 1, array(''), 1);
               
                $bc = Model::load('BlogCategory');
                $bc->createForBlogItem($_POST['category'], $b->id);

                Service::processTags($b, $tags_arr, $cats_arr);
                $this->redirect('admin/blog');
            }
        }
    }

    public function edit_blog()
    {
        $c = Model::load('BlogCategory');
        $cats = $c->getAllCustom(Model::getTable('BlogCategory'), '');
        $cats_arr = array();
        foreach ($cats as $index => $item) {
            $id = $item['id'];
            $cats_arr[$id] = $item['label'];
        }

        $this->presenter->assign('cats', $cats_arr);

        if (isset($_POST['cancel'])) {
            $this->redirect('admin/blog/view/'.$_POST['id']);
        } elseif (isset($_POST['save'])) {
            $b = Model::load('BlogItem');
            $b->id = $_POST['id'];
            $this->assertAuthorBlog($_POST['id']);

            $tags_arr = $b->buildTags();

            $b->load(Model::getTable('BlogItem'));

            $b->assignFromPost(array('stamp', 'id', 'tags', 'user_id', 'status'));

            $b->validates($tags_arr);
            $b->checkForDuplicates($tags_arr);

            if ($b->hasValErrors()) {
                $b->heading = $_POST['heading'];
                $b->body = $_POST['body'];
                $this->presenter->assign('blog', $b);
                $this->presenter->assign('blog_tags', $_POST['tags']);
                $this->presenter->assign('blog_cats', $_POST['category']);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $bi = Model::load('BlogImage');

                $images = $bi->getForIDs(array($b->id));
                
                $b->body = DI::getContainer()
                    ->get('BlogUtil')
                    ->reverseParseBlogImages($b->body);

                $b->save(Model::getTable('BlogItem'), array(''), 1);
                $bc = Model::load('BlogCategory');
                $bc->removeForBlogItem($b->id);
                $bc->createForBlogItem($_POST['category'], $b->id);

                Service::processTags($b, $tags_arr);
                $this->redirect('admin/blog/view/'.$b->id);
            }
        } else {
            $b = Model::load('BlogItem');
            $b->id = $_GET['id'];
            $this->assertAuthorBlog($_GET['id']);
            $b->load();

            //	$b->body = preg_replace('!<img src="http://'.WEB_ROOT.PUBLIC_DIR.'/uploads/(.*?)" alt="(.*?)" />!m', '<img src="" alt="$2" />', $b->body);

            $this->presenter->assign('blog', $b);

            // categories
            $bc = Model::load('BlogCategory');
            $sql = ' WHERE blog_id = '.$b->id;
            $blog_cats = $bc->getCategoriesForBlogItem($b->id);
            $this->assign('blog_cats', $blog_cats);

            // get tags
            $bt = Model::load('BlogTag');
            $tags_arr = $bt->getTags($b->id);
            $tags = implode(', ', $tags_arr);
            $this->presenter->assign('blog_tags', $tags);
        }

        $this->setTemplate('elib:/admin/blog/edit_blog.tpl');
    }



    // blog category stuff
    public function add_cat()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            
            if($_GET['id'] < 1) {
                $_GET['id'] = null;
            }

            $b = Model::load('BlogCategory');
            $b->blog_category_id = $_GET['id'];
            $b->label = 'New Category';
            $b->insert(Model::getTable('BlogCategory'), 1, array(), 0);
        }

        $this->redirect('admin/blog/category/'.$_GET['id']);
    }

    public function category()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->setTemplate('elib:/admin/blog/blog_cat.tpl');
        $ui_array = array('id');
        $this->loadUIVars('ui_blog_cats', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }

        $this->buildNav();
        $this->presenter->assign('blog_cat_id', $_GET['id']);
        $this->presenter->assign('class', 'blog_cat');
    }

    public function assertID()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function buildNav()
    {
        //$this->assertID();

        if (!isset($_GET['collapsed']) || !is_numeric($_GET['collapsed'])) {
            $_GET['collapsed'] = 0;
        }

        $b = Model::load('BlogCategory');
        $b->id = $_GET['id'];
        $b->load();

        $bt = new BlogCatTree($b, 1, $_GET['collapsed']);
        $this->presenter->assign('banners', $bt->getMarkup());
        $this->presenter->assign('banner', $b);
    }

    public function delete_category()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->assertID();
        $b = Model::load('BlogCategory');
        $b->id = $_GET['id'];
        $b->load();
        if ($b->hasCats($b->id)) {
            $this->redirect('admin/blog/category/'.$b->id);
        } else {
            $b->delete(Model::getTable('BlogCategory'));
            $this->redirect('admin/blog/category/'.$b->blog_category_id);
        }
    }

    public function rename_category()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->buildNav();
        if (isset($_POST['save'])) {
            $b = Model::load('BlogCategory');
            $b->id = $_POST['id'];
            $b->load();
            $b->label = $_POST['label'];
            $b->validates();
            if ($b->hasValErrors()) {
                $this->presenter->assign('blog_category', $b);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $b->save(Model::getTable('BlogCategory'), array(), 1);
                $this->redirect('admin/blog/category/'.$b->id);
            }
        } else {
            $b = Model::load('BlogCategory');
            $b->id = $_GET['id'];
            $b->load();
            $this->presenter->assign('blog_category', $b);
        }
        $this->setTemplate('elib:/admin/blog/blog_cat.tpl');
        $this->assign('class', 'blog_cat');
        $this->assign('event', 'rename');
    }



    public function remove_attachment()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            $_GET['id'] = 0;
        }

        $a = Model::load('BlogAttachment');

        $a->id = $_GET['id'];
        $a->load(Model::getTable('BlogAttachment'));
        $this->assertAuthorBlog($a->blog_id);

        $u = new Upload();
        if($u->remove(array($a->filename)))
        {
            $a->delete(Model::getTable('BlogAttachment'));
        }
        $this->redirect('admin/blog/view/'.$a->blog_id);
    }


    private function uploadAttachment()
    {
        $_GET['id'] = $_POST['id'];

        $u = new Upload();
        
        if($u->getError() != '')
        {

            $this->presenter->assign('error', $u->error);
        }
        else
        {
            $ba = Model::load('BlogAttachment');
            $ba->filename = $u->getFile();
            $ba->blog_id = $_GET['id'];
            $ba->insert(Model::getTable('BlogAttachment'), 1, array(), 0); 
            //$this->redirect('admin/blog/view/'.$_GET['id']);
        }    
    }

    public function blog_images()
    {
        $id = $_GET['id'];
        $this->assertAuthorBlog($id);
        $image = Model::load('BlogImage');
        $images = $image->getForIDs(array($id));
        $this->assign('images', $images[$id]);
        $this->setTemplate('elib://admin/blog/blog_images.tpl');
        $this->assign('blog_id', $id);
    }

    public function preview()
    {
        $fc = new BlogFrontControllerNew($this->boot);
        $fc->item(true);
        $this->setTemplate('elib:/blog/blog_item.tpl');

    }
}
