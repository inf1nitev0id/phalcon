<?php
class UsersController extends \Phalcon\Mvc\Controller
{
  public function indexAction(int $page = 1)
  {
    // $users = Users::find(); # вот одной этой строки достаточно, чтобы всё зависло
    # поэтому тут будет чтение из файла
    $RECORDS_ON_PAGE = 5;
    $json = file_get_contents('webhooks.json');
    $array = json_decode($json, true) ?? [];
    $this->view->page = $page;
    $this->view->pages = ceil(count($array) / $RECORDS_ON_PAGE);
    $this->view->users = array_slice($array, ($page - 1) * $RECORDS_ON_PAGE, $RECORDS_ON_PAGE);
  }

  private function getUserByHash(int $hash) {
    $json = file_get_contents('webhooks.json');
    $array = json_decode($json, true) ?? [];
    foreach ($array as $key => $user) {
      if ($user['hash'] == $hash) {
        return $user;
      }
    }
    return null;
  }

  public function editAction(int $hash) {
    $this->view->hash = $hash;
    if ($user = $this->getUserByHash($hash)) {
      $this->view->error = null;
      $this->view->user = $user;
    } else {
      $this->view->error = "Пользователь с таких хешем отсутствует.";
    }
  }

  public function editConfirmAction() {
    $json = file_get_contents('webhooks.json');
    $array = json_decode($json, true) ?? [];
    try {
      foreach ($array as $key => $user) {
        if ($user['hash'] == $this->request->getPost('hash')) {
          $array[$key]['data'] = json_decode($this->request->getPost('data'), true, 512, JSON_THROW_ON_ERROR);
          $array[$key]['name'] = $this->request->getPost('name');
          $array[$key]['family'] = $this->request->getPost('family');
          $array[$key]['update'] = $this->request->getPost('update');
        }
      }
      file_put_contents('webhooks.json', json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      $response = new Phalcon\Http\Response();
      $response->redirect('/users');
      $response->send();
    } catch (JsonException $exc) {
      echo $exc->getTraceAsString();
    }
  }

  public function deleteAction(int $hash) {
    $json = file_get_contents('webhooks.json');
    $array = json_decode($json, true) ?? [];
    foreach ($array as $key => $user) {
      if ($user['hash'] == $hash) {
        array_splice($array, $key, 1);
        file_put_contents('webhooks.json', json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $response = new Phalcon\Http\Response();
        $response->redirect('/users');
        $response->send();
      }
    }
    echo "Пользователь с таких хешем отсутствует.";
  }
}
