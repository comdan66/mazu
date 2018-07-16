<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Article extends AdminCrudController {
  
  public function __construct() {
    parent::__construct();

    if (in_array(Router::methodName(), ['edit', 'update', 'delete', 'show', 'enable']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Article::one('id = ?', $id))))
        Url::refreshWithFlash(Url::base('admin/tags'), ['msg' => '找不到資料。']);

    $this->view->with('title', '文章管理')
               ->with('currentUrl', Url::base('admin/articles'));
  }

  public function index() {
    $list = AdminList::model('\M\Article', ['include' => 'tags'])
              ->input('ID', 'id = ?')
              ->input('標題', 'title LIKE ?')
              ->checkboxs('狀態', 'enable IN (?)', items(array_keys(\M\Article::ENABLE), \M\Article::ENABLE))
              ->setAddUrl(Url::base('admin/articles/add'));

    return $this->view->setPath('admin/Article/index.php')
                      ->with('list', $list);
  }
  
  public function add() {
    $tags = \M\Tag::all(['select' => 'id, name', 'where' => ['enable = ?', \M\Tag::ENABLE_YES], 'toArray' => true]);

    $form = AdminForm::createAdd()
            ->setFlash($this->flash['params'])
            ->setActionUrl(Url::base('admin/articles'))
            ->setBackUrl(Url::base('admin/articles/'), '回列表');

    return $this->view->setPath('admin/Article/add.php')
                      ->with('form', $form)
                      ->with('tags', $tags);
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

      // tagIds
      $posts['tagIds'] = isset($posts['tagIds']) ? array_map(function($tagId) {
        return \M\Tag::one(['select' => 'id', 'where' => ['id = ? AND enable = ?', $tagId, \M\Tag::ENABLE_YES]])->id;
      }, $posts['tagIds']) : [];
      $posts['tagIds'] || Validator::error('沒有選擇標籤！');

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

      foreach ($posts['tagIds'] as $tagId)
        if (!\M\ArticleTagMapping::create(['articleId' => $obj->id, 'tagId' => $tagId]))
          return false;
      
      return true;
    };

    $posts = Input::post();
    $files = Input::file();

    $error = '';
    $error || $error = validator($validator, $posts, $files);
    $error || $error = transaction($transaction, $posts, $files);
    $error && Url::refreshWithFlash(Url::base('admin/articles/add'), ['msg' => $error, 'params' => $posts]);
    Url::refreshWithFlash(Url::base('admin/articles'), '新增成功！');
  }
  
  public function edit($id) {
    $tags = \M\Tag::all(['select' => 'id, name', 'where' => ['enable = ?', \M\Tag::ENABLE_YES], 'toArray' => true]);

    $form = AdminForm::createEdit($this->obj)
            ->setFlash($this->flash['params'])
            ->setActionUrl(Url::base('admin/articles/' . $this->obj->id))
            ->setBackUrl(Url::base('admin/articles/'), '回列表');

    return $this->view->setPath('admin/Article/edit.php')
                      ->with('obj', $this->obj)
                      ->with('tags', $tags)
                      ->with('form', $form);
  }
  
  public function update($id) {
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

      // tagIds
      $posts['tagIds'] = isset($posts['tagIds']) ? array_map(function($tagId) {
        return \M\Tag::one(['select' => 'id', 'where' => ['id = ? AND enable = ?', $tagId, \M\Tag::ENABLE_YES]])->id;
      }, $posts['tagIds']) : [];
      $posts['tagIds'] || Validator::error('沒有選擇標籤！');

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

      $oriIds = array_column(\M\toArray($this->obj->tags), 'id');
      $delIds = array_diff($oriIds, $posts['tagIds']);
      $addIds = array_diff($posts['tagIds'], $oriIds);

      foreach ($delIds as $delId)
        if ($mapping = \M\ArticleTagMapping::one('articleId = ? AND tagId = ?', $this->obj->id, $delId))
          if (!$mapping->delete())
            return false;

      foreach ($addIds as $addId)
        if (!\M\ArticleTagMapping::create(['articleId' => $this->obj->id, 'tagId' => $addId]))
          return false;
      
      return true;
    };

    $posts = Input::post();
    $files = Input::file();

    $error = '';
    $error || $error = validator($validator, $posts, $files);
    $error || $error = transaction($transaction, $posts, $files);
    $error && Url::refreshWithFlash(Url::base('admin/articles/' . $this->obj->id . '/edit'), ['msg' => $error, 'params' => $posts]);
    Url::refreshWithFlash(Url::base('admin/articles'), '修改成功！');
  }
  
  public function show($id) {
    $show = AdminShow::create($this->obj)
                     ->setBackUrl(Url::base('admin/articles/'), '回列表');

    return $this->view->setPath('admin/Article/show.php')
                      ->with('show', $show)
                      ->with('obj', $this->obj);
  }
  
  public function delete($id) {
    $error = transaction(function() {
      return $this->obj->delete();
    });

    Url::refreshWithFlash(Url::base('admin/articles/'), $error ? ['msg' => $error] : '刪除成功！');
  }

  public function enable($obj) {
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
