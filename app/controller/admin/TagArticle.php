<?php defined('MAZU') || exit('此檔案不允許讀取！');

class TagArticle extends AdminCrudController {
  private $parent, $articleIds;
  
  public function __construct() {
    parent::__construct();
    
    if (!(($tagId = Router::params('tagId')) !== null && ($this->parent = \M\Tag::one('id = ?', $tagId))))
      Url::refreshWithFailureFlash(Url::toRouter('AdminTagIndex'), '找不到資料！');

    $this->articleIds = array_column(\M\toArray($this->parent->articleTagMappings), 'articleId');

    if (in_array(Router::methodName(), ['edit', 'update', 'delete', 'show', 'enable']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Article::one('id = ? AND id IN(?)', $id, $this->articleIds))))
        Url::refreshWithFailureFlash(Url::toRouter('AdminTagArticleIndex', $this->parent), '找不到資料！');

    $this->view->with('title', '標籤文章')
               ->with('currentUrl', Url::toRouter('AdminTagIndex'))
               ->with('parent', $this->parent);
  }

  public function index() {
    $where = Where::create('id IN (?)', $this->articleIds);

    $list = AdminList::model('\M\Article', ['include' => ['tags', 'images'], 'where' => $where])
              ->input('ID', 'id = ?')
              ->input('標題', 'title LIKE ?')
              ->checkboxs('狀態', 'enable IN (?)', items(array_keys(\M\Article::ENABLE), \M\Article::ENABLE))
              ->setAddUrl(Url::toRouter('AdminTagArticleAdd', $this->parent));

    return $this->view->setPath('admin/TagArticle/index.php')
                      ->with('list', $list);
  }
  
  public function add() {
    $form = AdminForm::createAdd()
            ->setFlash($this->flash['params'])
            ->setActionUrl(Url::toRouter('AdminTagArticleCreate', $this->parent))
            ->setBackUrl(Url::toRouter('AdminTagArticleIndex', $this->parent), '回列表');

    return $this->view->setPath('admin/TagArticle/add.php')
                      ->with('form', $form);
  }
  
  public function create() {
    $validator = function(&$posts, &$files) {
      // enable
      isset($posts['enable']) || Validator::error('狀態不存在！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態不存在！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Article::ENABLE) || Validator::error('狀態錯誤！');

      // cover
      isset($files['cover']) || Validator::error('封面不存在！');
      uploadFileInFormats($files['cover'], ['jpg', 'gif', 'png']) || Validator::error('封面格式不符！');
      $files['cover']['size'] >= 1 && $files['cover']['size'] <= 10 * 1024 * 1024 || Validator::error('封面檔案大小錯誤！');

      // images
      isset($files['images']) || $files['images'] = [];
      $files['images'] = array_filter($files['images'], function($image) { return uploadFileInFormats($image, ['jpg', 'gif', 'png']) && $image['size'] >= 1 && $image['size'] <= 10 * 1024 * 1024; });
      $files['images'] || Validator::error('組圖不存在！');

      // tagIds
      isset($posts['tagIds']) || $posts['tagIds'] = [$this->parent->id];

      // title
      isset($posts['title']) || Validator::error('標題不存在！');
      $posts['title'] = strip_tags(trim($posts['title']));
      $posts['title'] || Validator::error('標題不存在！');
      mb_strlen($posts['title']) <= 190 || Validator::error('標題長度錯誤！');

      // content
      isset($posts['content']) || Validator::error('內容不存在！');
      $posts['content'] = trim($posts['content']);
      $posts['content'] || Validator::error('內容不存在！');
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

    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminTagArticleAdd', $this->parent), $error, $posts);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminTagArticleIndex', $this->parent), '新增成功！');
  }
  
  public function edit() {
    $form = AdminForm::createEdit($this->obj)
            ->setFlash($this->flash['params'])
            ->setActionUrl(Url::toRouter('AdminTagArticleUpdate', $this->parent, $this->obj))
            ->setBackUrl(Url::toRouter('AdminTagArticleIndex', $this->parent), '回列表');

    return $this->view->setPath('admin/TagArticle/edit.php')
                      ->with('form', $form);
  }
  
  public function update() {
    $validator = function(&$posts, &$files) {
      // enable
      isset($posts['enable']) || Validator::error('狀態不存在！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態不存在！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Article::ENABLE) || Validator::error('狀態錯誤！');

      // cover
      if (!(string)$this->obj->cover) {
        isset($files['cover']) || Validator::error('封面不存在！');
        uploadFileInFormats($files['cover'], ['jpg', 'gif', 'png']) || Validator::error('封面格式不符！');
        $files['cover']['size'] >= 1 && $files['cover']['size'] <= 10 * 1024 * 1024 || Validator::error('封面檔案大小錯誤！');
      }

      // images
      isset($files['images']) || $files['images'] = [];
      $files['images'] = array_filter($files['images'], function($image) { return uploadFileInFormats($image, ['jpg', 'gif', 'png']) && $image['size'] >= 1 && $image['size'] <= 10 * 1024 * 1024; });

      // _images
      isset($posts['_images']) || $posts['_images'] = [];
      $posts['_images'] = \M\ArticleImage::arr('id', ['where' => ['id IN (?)', $posts['_images']]]);
      $posts['_images'] || $files['images'] || Validator::error('組圖不存在！');

      // title
      isset($posts['title']) || Validator::error('標題不存在！');
      $posts['title'] = strip_tags(trim($posts['title']));
      $posts['title'] || Validator::error('標題不存在！');
      mb_strlen($posts['title']) <= 190 || Validator::error('標題長度錯誤！');

      // content
      isset($posts['content']) || Validator::error('內容不存在！');
      $posts['content'] = trim($posts['content']);
      $posts['content'] || Validator::error('內容不存在！');
    };

    $transaction = function(&$posts, &$files) {
      if (!($this->obj->columnsUpdate($posts) && $this->obj->save()))
        return false;

      if (!$this->obj->putFiles($files))
        return false;

      // image
      $oriIds = array_column(\M\toArray($this->obj->images), 'id');
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
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminTagArticleEdit', $this->parent, $this->obj), $error, $posts);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminTagArticleIndex', $this->parent), '修改成功！');
  }
  
  public function show() {
    $show = AdminShow::create($this->obj)
                      ->setBackUrl(Url::toRouter('AdminTagArticleIndex', $this->parent), '回列表');

    return $this->view->setPath('admin/TagArticle/show.php')
                      ->with('show', $show);
  }
  
  public function delete() {
    $error = transaction(function() {
      return $this->obj->delete();
    });

    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminTagArticleIndex', $this->parent), $error);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminTagArticleIndex', $this->parent), '刪除成功！');
  }

  public function enable() {
    $validator = function(&$posts) {
      isset($posts['enable']) || Validator::error('狀態不存在！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態不存在！');
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
