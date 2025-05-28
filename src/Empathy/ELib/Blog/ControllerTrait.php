<?php

namespace Empathy\ELib\Blog;

use Empathy\ELib\File\Image as ImageUpload;
use Empathy\ELib\File\Upload;
use Empathy\MVC\Model;
use Empathy\MVC\DI;
use Empathy\MVC\RequestException;
use Empathy\MVC\Session;
use Empathy\ELib\Storage\BlogItemStatus;
use Empathy\ELib\Storage\BlogItem;
use Empathy\ELib\Storage\BlogTag;
use Empathy\ELib\Storage\BlogCategory;
use Empathy\ELib\Storage\UserAccess;
use Empathy\ELib\Storage\UserItem;
use Empathy\ELib\Storage\BlogImage;
use Empathy\ELib\Storage\BlogRevision;
use Empathy\ELib\Storage\BlogAttachment;
use Empathy\MVC\Config;


define('REQUESTS_PER_PAGE', 12);


trait ControllerTrait
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
        $ua = new UserAccess();
        if (!($u->auth < $ua->getLevel('admin')) && is_null($authorId)) {
            return;
        }

        if ($authorId) {
            $b = Model::load(BlogItem::class);
            $sql = 'select id from ' . Model::getTable(BlogItem::class)
                . ' where user_id = ?'
                . ' and id = ?';
            $result = $b->query($sql, '', [$authorId, $id]);
            $authed = $result->rowCount() === 1;
        }
        if (!$authed) {
            throw new RequestException('Denied', RequestException::NOT_AUTHORIZED);
        }
    }

    public function default_event()
    {
        $ui_array = ['page', 'status'];
        $this->loadUIVars('ui_blog', $ui_array);
        
        $_GET['page'] = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) ?? 1 : 1;
        $_GET['status'] = isset($_GET['status']) && is_numeric($_GET['status']) ? intval($_GET['status']) ?? 1 : 1;

        $this->presenter->assign('page', $_GET['page']);
        $this->presenter->assign('status', $_GET['status']);

        $admin = false;

        // is admin user?
        $u = Model::load(UserItem::class);
        $u->load(Session::get('user_id'));
        $ua = new UserAccess();
        if ($u->auth >= $ua->getLevel('admin')) {
            $admin = true;
        }
        $this->presenter->assign('super', $admin);

        $c = Model::load(BlogCategory::class);
        $cats = $c->getAll();
        $cats_arr = [];
        foreach ($cats as $index => $item) {
            $id = $item['id'];
            $cats_arr[$id] = $item['label'];
        }

        $b = Model::load(BlogItem::class);
        $blogs = [];

        $params = [];
        $select = '*,t1.id AS id, t4.body_revision';
        $sql = ' WHERE status = ?';
        $params[] = $_GET['status'];

        if (!$admin) {
            $sql .= ' AND user_id = ?';
            $params[] = $this->stash->get('authorId');
        }

        $sql .= ' AND t1.user_id = t2.id';
        $sql .= ' ORDER BY stamp DESC';

        $leftJoins = ' left join'
            .' (select max(id) as max, any_value(blog_id) as blog_id from blog_revision group by blog_id) t3'
            .' on t3.blog_id = t1.id'
            .' left join (select id, body as body_revision from blog_revision) t4 on t3.max = t4.id,';

        $p_nav = $b->getPaginatePagesSimpleJoin(
            $select,
            Model::getTable(UserItem::class),
            $sql,
            $_GET['page'],
            REQUESTS_PER_PAGE,
            $leftJoins,
            $params
        );
        $this->presenter->assign('p_nav', $p_nav);

        $this->presenter->assign('status', $_GET['status']);

        $blogs = $b->getAllCustomPaginateSimpleJoin(
            $select,
            Model::getTable(UserItem::class),
            $sql,
            $_GET['page'],
            REQUESTS_PER_PAGE,
            $leftJoins,
            $params
        );
        

        foreach ($blogs as $index => $item) {
            $blog_cats = $c->getCategoriesForBlogItem($blogs[$index]['id']);

            $cats = [];
            foreach ($blog_cats as $bc_id) {
                $cats[] = $cats_arr[$bc_id];
            }
            $blogs[$index]['category'] = implode(', ', $cats);

            if (isset($blogs[$index]['body_revision'])) {
                $blogs[$index]['body'] = $blogs[$index]['body_revision'];
            }
        }


        $this->setTemplate('elib:admin/blog/blog_admin.tpl');
        $this->presenter->assign('blogs', $blogs);
    }

    private function uploadImage()
    {
        $_GET['id'] = $_POST['id'];
        $sizes = [
            ['l', 800, 600],
            ['tn', 200, 200],
            ['mid', 468, 5000]
        ];
        $u = new ImageUpload('blog', true, $sizes);

        if ($u->error != '') {
            $this->presenter->assign('error', $u->error);
        } else {
            $bi = Model::load(BlogImage::class);
            $bi->filename = $u->getFile();
            $bi->blog_id = $_GET['id'];
            $bi->image_width = $u->getDimensions()[0];
            $bi->image_height = $u->getDimensions()[1];
            $bi->insert();
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
        $i = Model::load(BlogImage::class);
        $i->load($_GET['id']);

        $u = new ImageUpload('blog', false, []);
        if ($u->remove([$i->filename])) {
            $i->delete();
        }

        $this->redirect('admin/blog/view/'.$i->blog_id);
    }

    public function delete()
    {
        $b = Model::load(BlogItem::class);
        $this->assertAuthorBlog($_GET['id']);
        $b->load($_GET['id']);
        $b->status = BlogItemStatus::DELETED;
        $b->save();

        Service::removeFromIndex($b);

        $this->redirect('admin/blog/?page=1&status=2');
    }

    public function redraft()
    {
        $b = Model::load(BlogItem::class);
        $this->assertAuthorBlog($_GET['id']);
        $b->load($_GET['id']);
        $b->status = BlogItemStatus::DRAFT;
        $b->save();

        $this->clearCache();

        Service::removeFromIndex($b);

        $this->redirect('admin/blog/view/'.$b->id);
    }

    public function publish()
    {
        $b = Model::load(BlogItem::class);
        $this->assertAuthorBlog($_GET['id']);
        $b->load($_GET['id']);
        if (isset($_GET['stamp']) && $_GET['stamp'] == 1) {
            $b->stamp = date('Y-m-d H:i:s', time());
        }
        $b->status = BlogItemStatus::PUBLISHED;
        $b->save();

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

        $b = Model::load(BlogItem::class);
        $b->load($_GET['id']);
        
        $r = Model::load(BlogRevision::class);
        list($b) = $r->loadSaved($b);

        $u = Model::load(UserItem::class);
        $u->load($b->user_id);

        $this->presenter->assign('author', $u->username);
        $this->presenter->assign('blog', $b);

        $bi = Model::load(BlogImage::class);
        $sql = ' WHERE blog_id = ?';
        $images = $bi->getAllCustom($sql, [$b->id]);

        /*
          $image = [];
          foreach ($images as $item) {
          array_push($image, $item['filename']);
          }
        */

        $this->presenter->assign('images', $images);

        $ba = Model::load(BlogAttachment::class);
        $sql = ' WHERE blog_id = ?';
        $attachments = $ba->getAllCustom($sql, [$b->id]);

        /*
          $image = [];
          foreach($images as $item)
          {
          array_push($image, $item['filename']);
          }
        */
        $this->presenter->assign('attachments', $attachments);


        // get tags
        $bt = Model::load(BlogTag::class);
        $tags_arr = $bt->getTags($b->id);
        $tags = implode(', ', $tags_arr);
        $this->presenter->assign('blog_tags', $tags);

        $this->setTemplate('elib:admin/blog/view_blog_item.tpl');
    }

    public function create()
    {
        $c = Model::load(BlogCategory::class);
        $cats = $c->getAll();
        $cats_arr = [];
        foreach ($cats as $index => $item) {
            $id = $item['id'];
            $cats_arr[$id] = $item['label'];
        }

        $this->presenter->assign('cats', $cats_arr);

        $this->setTemplate('elib:admin/blog/create_blog.tpl');

        if (isset($_POST['cancel'])) {
            $this->redirect('admin/blog');
        } elseif (isset($_POST['save'])) {


            $b = Model::load(BlogItem::class);
            $tags_arr = $b->buildTags($_POST['tags']); // errors ?

            $b->heading = $_POST['heading'];
            $b->body = $_POST['body'];

            $b->status = BlogItemStatus::DRAFT;
            $b->slug = $_POST['slug'];
            $b->setCategory($_POST['category'] ?? []);

            $b->checkForDuplicates($tags_arr);
            $b->validates();

            if ($b->hasValErrors()) {
                $this->presenter->assign('blog', $b);
                $this->presenter->assign('blog_tags', $_POST['tags']);
                $this->presenter->assign('errors', $b->getValErrors());
                $this->assign('blog_cats', $b->getCategory());
            } else {
                $b->assignFromPost(['user_id', 'id', 'stamp', 'tags', 'status']);
                $b->user_id = Session::get('user_id');
                $b->stamp = date('Y-m-d H:i:s', time());              
                $b->id = $b->insert();
               
                $r = Model::load(BlogRevision::class);
                $r->blog_id = $b->id;
                $r->body = $b->body;
                $r->blog_id = $b->id;
                $r->body = $b->body;
                $r->stamp = 'MYSQLTIME';
                $revisionMeta = new \stdClass();
                $revisionMeta->category = $_POST['category'];
                $revisionMeta->tags = $tags_arr;
                $revisionMeta->slug = $b->slug;
                $revisionMeta->heading = $b->heading;
                $r->meta = json_encode($revisionMeta);
                $r->insert();

                $bc = Model::load(BlogCategory::class);
                $bc->createForBlogItem($b->getCategory(), $b->id);

                Service::processTags($b, $tags_arr, $cats_arr);
                $this->redirect('admin/blog');
            }
        }
    }

    public function edit_blog()
    {
        $c = Model::load(BlogCategory::class);
        $cats = $c->getAll();
        $cats_arr = [];
        foreach ($cats as $index => $item) {
            $id = $item['id'];
            $cats_arr[$id] = $item['label'];
        }

        $this->presenter->assign('cats', $cats_arr);
        $revisions = [];

        if (isset($_POST['cancel'])) {
            $this->redirect('admin/blog/view/'.$_POST['id']);
        } elseif (isset($_POST['save'])) {
            $b = Model::load(BlogItem::class);
            $this->assertAuthorBlog($_POST['id']);

            $tags_arr = $b->buildTags($_POST['tags']);
            $b->setCategory($_POST['category'] ?? []);

            $b->load($_POST['id']);
            $b->assignFromPost(['stamp', 'id', 'tags', 'user_id', 'status']);

            $b->validates($tags_arr);
            $b->checkForDuplicates($tags_arr);

            if ($b->hasValErrors()) {
                $b->heading = $_POST['heading'];
                $b->body = $_POST['body'];
                $this->presenter->assign('blog', $b);
                $this->presenter->assign('blog_tags', $_POST['tags']);
                $this->presenter->assign('blog_cats', $b->getCategory());
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $bi = Model::load(BlogImage::class);

                $images = $bi->getForIDs([$b->id]);
                
                $b->body = DI::getContainer()
                    ->get('BlogUtil')
                    ->reverseParseBlogImages($b->body);

                $b->save();
                $bc = Model::load(BlogCategory::class);
                $bc->removeForBlogItem($b->id);
                $bc->createForBlogItem($_POST['category'], $b->id);

                $r = Model::load(BlogRevision::class);
                $r->blog_id = $b->id;
                $r->body = $b->body;
                $r->stamp = 'MYSQLTIME';
                $revisionMeta = new \stdClass();
                $revisionMeta->category = $_POST['category'];
                $revisionMeta->tags = $tags_arr;
                $revisionMeta->slug = $b->slug;
                $revisionMeta->heading = $b->heading;
                $r->meta = json_encode($revisionMeta);
                $r->insert();

                Service::processTags($b, $tags_arr);
                $this->clearCache();
                $this->redirect('admin/blog/view/'.$b->id);
            }
        } else {
            $b = Model::load(BlogItem::class);
            $this->assertAuthorBlog($_GET['id']);
            $b->load($_GET['id']);

            //	$b->body = preg_replace('!<img src="http://'.WEB_ROOT.PUBLIC_DIR.'/uploads/(.*?)" alt="(.*?)" />!m', '<img src="" alt="$2" />', $b->body);

            $revision = 0;
            if (isset($_GET['revision']) && is_numeric($_GET['revision'])) {
                $revision = $_GET['revision'];
            }

            $r = Model::load(BlogRevision::class);
            list($b, $revisionMeta) = $r->loadSaved($b, $revision);


            $this->presenter->assign('blog', $b);

            // categories
            if (isset($revisionMeta['category'])) {
                $blogCats = $revisionMeta['category'];
            } else {
                $bc = Model::load(BlogCategory::class);
                $sql = ' WHERE blog_id = '.$b->id;
                $blogCats = $bc->getCategoriesForBlogItem($b->id);
            }
            $this->assign('blog_cats', $blogCats);

            // get tags
            if (isset($revisionMeta['tags'])) {
                $tagsArr = $revisionMeta['tags'];
            } else {
                $bt = Model::load(BlogTag::class);
                $tagsArr = $bt->getTags($b->id);
            }
            $this->presenter->assign('blog_tags', implode(', ', $tagsArr));

            $this->assign('revision', $revision ?? '');
            $revisions = $r->loadAll($b);
        }
        $this->assign('revisions', $revisions);
        $this->setTemplate('elib:admin/blog/edit_blog.tpl');
    }



    // blog category stuff
    public function add_cat()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            if($_GET['id'] < 1) {
                $_GET['id'] = null;
            }

            $b = Model::load(BlogCategory::class);
            $b->blog_category_id = $_GET['id'];
            $b->label = 'New Category';
            $b->position = 0;
            $b->insert();
        }

        $this->redirect('admin/blog/category/'.$_GET['id']);
    }

    public function category()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->setTemplate('elib:admin/blog/blog_cat.tpl');
        $ui_array = ['id'];
        $this->loadUIVars('ui_blog_cats', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }

        $this->buildNav();
        $this->presenter->assign('blog_cat_id', $_GET['id']);
        $this->presenter->assign('class', 'blog_cat');
    }

    public function cat_sort() {
        header('Content-type: application/json');

        $position = 1;
        foreach($_POST as $type => $value) {
            $object = Model::load(BlogCategory::class);

            foreach ($value as $id) {
                $object->load($id);
                $object->position = $position;
                $object->save();
                $position++;
            }
        }
        echo json_encode(1);
        return false;
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

        $b = Model::load(BlogCategory::class);
        $b->load($_GET['id']);

        $bt = new BlogCatTree($b, 1, $_GET['collapsed']);
        $this->presenter->assign('banners', $bt->getMarkup());
        $this->presenter->assign('banner', $b);
    }

    public function delete_category()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->assertID();
        $b = Model::load(BlogCategory::class);
        $b->load($_GET['id']);
        if ($b->hasCats($b->id)) {
            $this->redirect('admin/blog/category/'.$b->id);
        } else {
            $b->delete();
            $this->redirect('admin/blog/category/'.$b->blog_category_id);
        }
    }

    public function rename_category()
    {
        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->buildNav();
        if (isset($_POST['save'])) {
            $b = Model::load(BlogCategory::class);
            $b->load($_POST['id']);
            $b->label = $_POST['label'];
            $b->validates();
            if ($b->hasValErrors()) {
                $this->presenter->assign('blog_category', $b);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $b->save();
                $this->redirect('admin/blog/category/'.$b->id);
            }
        } else {
            $b = Model::load(BlogCategory::class);
            $b->load($_GET['id']);
            $this->presenter->assign('blog_category', $b);
        }
        $this->setTemplate('elib:admin/blog/blog_cat.tpl');
        $this->assign('class', 'blog_cat');
        $this->assign('event', 'rename');
    }



    public function remove_attachment()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id']))
        {
            $_GET['id'] = 0;
        }

        $a = Model::load(BlogAttachment::class);
        $a->load($_GET['id']);
        $this->assertAuthorBlog($a->blog_id);

        $u = new Upload();
        if($u->remove([$a->filename]))
        {
            $a->delete();
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
            $ba = Model::load(BlogAttachment::class);
            $ba->filename = $u->getFile();
            $ba->blog_id = $_GET['id'];
            $ba->insert();
            //$this->redirect('admin/blog/view/'.$_GET['id']);
        }    
    }

    public function blog_images()
    {
        $id = $_GET['id'];
        $this->assertAuthorBlog($id);     
        $image = Model::load(BlogImage::class);
        $images = $image->getForIDs([$id]);
        $this->assign('images', count($images) ? $images[$id] : []);
        $this->setTemplate('elib:admin/blog/blog_images.tpl');
        $this->assign('blog_id', $id);
    }

    public function preview()
    {
        $fc = new BlogFrontControllerNew($this->boot);
        $fc->item(true);
        $this->setTemplate('elib:blog/blog_item.tpl');

    }

    public function edit_cat_meta()
    {

        DI::getContainer()->get('CurrentUser')->denyNotAdmin();
        $this->setTemplate('elib:admin/blog/blog_cat_meta.tpl');
        $ui_array = ['id'];
        $this->loadUIVars('ui_blog_cats_meta', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }

        $this->buildNav();
        $this->presenter->assign('blog_cat_id', $_GET['id']);
        
        if (isset($_POST['save'])) {
            $c = Model::load(BlogCategory::class);
            $c->load($_POST['id']);
            $c->meta = $_POST['meta'];

            $c->validates();
            if ($c->hasValErrors()) {
                $this->presenter->assign('category_item', $c);
                $this->presenter->assign('errors', $c->getValErrors());
            } else {
                $c->save();
                $this->clearCache();
                $this->redirect('admin/blog/category/'.$c->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/blog/category/'.$_POST['id']);
        }

        $c = Model::load(BlogCategory::class, $_GET['id']);
        $this->presenter->assign('cat_item', $c);
    }

}
