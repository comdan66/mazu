<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Article extends AdminCrudController {
  
  public function __construct() {
    parent::__construct();

    if (in_array(Router::methodName(), ['edit', 'update', 'delete', 'show', 'enable']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Article::one('id = ?', $id))))
        Url::refreshWithFailureFlash(Url::toRouter('AdminArticleIndex'), '找不到資料！');

    $this->view->with('title', '文章管理')
               ->with('currentUrl', Url::toRouter('AdminArticleIndex'));
  }

  public function index() {
    $list = AdminList::model('\M\Article', ['include' => ['tags', 'images']])
                     ->input('ID', 'id = ?')
                     ->input('標題', 'title LIKE ?')
                     ->checkboxs('狀態', 'enable IN (?)', items(array_keys(\M\Article::ENABLE), \M\Article::ENABLE))
                     ->setAddUrl(Url::toRouter('AdminArticleAdd'));
    
    return $this->view->setPath('admin/Article/index.php')
                      ->with('list', $list);
  }

  public function add() {
    $tags = \M\Tag::all(['select' => 'id, name', 'order' => 'sort DESC']);

    $form = AdminForm::createAdd()
                     ->setFlash($this->flash['params'])
                     ->setActionUrl(Url::toRouter('AdminArticleCreate'))
                     ->setBackUrl(Url::toRouter('AdminArticleIndex'), '回列表');

    return $this->view->setPath('admin/Article/add.php')
                      ->with('form', $form)
                      ->with('tags', $tags);
  }
  
  public function create() {
    $validator = function(&$posts, &$files) {
      // enable
      isset($posts['enable']) || Validator::error('狀態必填！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態必填！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Article::ENABLE) || Validator::error('狀態錯誤！');

      // cover
      isset($files['cover']) || Validator::error('封面必填！');
      uploadFileInFormats($files['cover'], ['jpg', 'gif', 'png']) || Validator::error('封面格式不符！');
      $files['cover']['size'] >= 1 && $files['cover']['size'] <= 10 * 1024 * 1024 || Validator::error('封面檔案大小錯誤！');

      // images
      isset($files['images']) || $files['images'] = [];
      $files['images'] = array_filter($files['images'], function($image) { return uploadFileInFormats($image, ['jpg', 'gif', 'png']) && $image['size'] >= 1 && $image['size'] <= 10 * 1024 * 1024; });
      $files['images'] || Validator::error('組圖必填！');

      // tagIds
      isset($posts['tagIds']) || $posts['tagIds'] = [];
      $posts['tagIds'] = \M\Tag::arr('id', ['where' => ['id IN (?)', $posts['tagIds']]]);
      $posts['tagIds'] || Validator::error('沒有選擇標籤！');

      // title
      isset($posts['title']) || Validator::error('標題必填！');
      $posts['title'] = strip_tags(trim($posts['title']));
      $posts['title'] || Validator::error('標題必填！');
      mb_strlen($posts['title']) <= 190 || Validator::error('標題長度錯誤！');

      // content
      isset($posts['content']) || Validator::error('內容必填！');
      $posts['content'] = trim($posts['content']);
      $posts['content'] || Validator::error('內容必填！');
    };

    $transaction = function(&$posts, &$files) {
      if (!$obj = \M\Article::create($posts))
        return false;

      if (!$obj->putFiles($files))
        return false;

      // tags
      foreach ($posts['tagIds'] as $tagId)
        if (!\M\ArticleTagMapping::create(['articleId' => $obj->id, 'tagId' => $tagId]))
          return false;

      // images
      foreach ($files['images'] as $image) {
        if (!$articleImage = \M\ArticleImage::create(['articleId' => $obj->id, 'pic' => '']))
          return false;

        if (!$articleImage->pic->put($image))
          return false;
      }
      
      return true;
    };

    $posts = Input::post();
    $posts['content'] = Input::post('content', false);
    $files = Input::file();

    $error = '';
    $error || $error = validator($validator, $posts, $files);
    $error || $error = transaction($transaction, $posts, $files);
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminArticleAdd'), $error, $posts);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminArticleIndex'), '新增成功！');
  }
  
  public function edit() {
    $tags = \M\Tag::all(['select' => 'id, name', 'order' => 'sort DESC']);

    $form = AdminForm::createEdit($this->obj)
                     ->setFlash($this->flash['params'])
                     ->setActionUrl(Url::toRouter('AdminArticleUpdate', $this->obj))
                     ->setBackUrl(Url::toRouter('AdminArticleIndex'), '回列表');

    return $this->view->setPath('admin/Article/edit.php')
                      ->with('tags', $tags)
                      ->with('form', $form);
  }
  
  public function update() {
    $validator = function(&$posts, &$files) {
      // enable
      isset($posts['enable']) || Validator::error('狀態必填！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態必填！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Article::ENABLE) || Validator::error('狀態錯誤！');

      // cover
      if (!(string)$this->obj->cover) {
        isset($files['cover']) || Validator::error('封面必填！');
        uploadFileInFormats($files['cover'], ['jpg', 'gif', 'png']) || Validator::error('封面格式不符！');
        $files['cover']['size'] >= 1 && $files['cover']['size'] <= 10 * 1024 * 1024 || Validator::error('封面檔案大小錯誤！');
      }

      // images
      isset($files['images']) || $files['images'] = [];
      $files['images'] = array_filter($files['images'], function($image) { return uploadFileInFormats($image, ['jpg', 'gif', 'png']) && $image['size'] >= 1 && $image['size'] <= 10 * 1024 * 1024; });

      // _images
      isset($posts['_images']) || $posts['_images'] = [];
      $posts['_images'] = \M\ArticleImage::arr('id', ['where' => ['id IN (?)', $posts['_images']]]);
      $posts['_images'] || $files['images'] || Validator::error('組圖必填！');

      // tagIds
      isset($posts['tagIds']) || $posts['tagIds'] = [];
      $posts['tagIds'] = \M\Tag::arr('id', ['where' => ['id IN (?)', $posts['tagIds']]]);
      $posts['tagIds'] || Validator::error('沒有選擇標籤！');

      // title
      isset($posts['title']) || Validator::error('標題必填！');
      $posts['title'] = strip_tags(trim($posts['title']));
      $posts['title'] || Validator::error('標題必填！');
      mb_strlen($posts['title']) <= 190 || Validator::error('標題長度錯誤！');

      // content
      isset($posts['content']) || Validator::error('內容必填！');
      $posts['content'] = trim($posts['content']);
      $posts['content'] || Validator::error('內容必填！');
    };

    $transaction = function(&$posts, &$files) {
      if (!($this->obj->columnsUpdate($posts) && $this->obj->save()))
        return false;

      if (!$this->obj->putFiles($files))
        return false;

      // tags
      $oriIds = arrayColumn($this->obj->tags, 'id');
      $delIds = array_diff($oriIds, $posts['tagIds']);
      $addIds = array_diff($posts['tagIds'], $oriIds);

      foreach ($delIds as $delId)
        if ($mapping = \M\ArticleTagMapping::one('articleId = ? AND tagId = ?', $this->obj->id, $delId))
          if (!$mapping->delete())
            return false;

      foreach ($addIds as $addId)
        if (!\M\ArticleTagMapping::create(['articleId' => $this->obj->id, 'tagId' => $addId]))
          return false;

      // image
      $oriIds = arrayColumn($this->obj->images, 'id');
      $delIds = array_diff($oriIds, $posts['_images']);

      foreach ($delIds as $delId)
        if ($articleImage = \M\ArticleImage::one('articleId = ?', $this->obj->id))
          if (!$articleImage->delete())
            return false;

      foreach ($files['images'] as $image) {
        if (!$articleImage = \M\ArticleImage::create(['articleId' => $this->obj->id, 'pic' => '']))
          return false;

        if (!$articleImage->pic->put($image))
          return false;
      }
      
      return true;
    };

    $posts = Input::post();
    $posts['content'] = Input::post('content', false);
    $files = Input::file();

    $error = '';
    $error || $error = validator($validator, $posts, $files);
    $error || $error = transaction($transaction, $posts, $files);
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminArticleEdit', $this->obj), $error, $posts);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminArticleIndex'), '修改成功！');
  }
  
  public function show() {
    $show = AdminShow::create($this->obj)
                     ->setBackUrl(Url::toRouter('AdminArticleIndex'), '回列表');

    return $this->view->setPath('admin/Article/show.php')
                      ->with('show', $show);
  }
  
  public function delete() {
    $error = transaction(function() {
      return $this->obj->delete();
    });

    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminArticleIndex'), $error);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminArticleIndex'), '刪除成功！');
  }

  public function enable() {
    $validator = function(&$posts) {
      isset($posts['enable']) || Validator::error('狀態必填！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態必填！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Article::ENABLE) || Validator::error('狀態錯誤！');
    };

    $transaction = function(&$posts) {
      return $this->obj->columnsUpdate($posts) && $this->obj->save();
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);

    return $error ? ['error' => $error] : $posts;
  }
}
