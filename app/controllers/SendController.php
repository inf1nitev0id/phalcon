<?php
class SendController extends \Phalcon\Mvc\Controller
{
  public function indexAction()
  {
    ;
  }

  private function write($new_record) {
    # тут должна была быть запись в БД, но вместо неё запись в файл
    $json = file_get_contents('webhooks.json');
    $array = json_decode($json, true) ?? [];
    $id = null;
    foreach ($array as $key => $record) {
      if ($record['hash'] === $new_record['hash']) {
        $id = $key;
        break;
      }
    }
    if ($id === null) {
      $array[] = $new_record;
    } else {
      $array[$id] = $new_record;
    }
    file_put_contents('webhooks.json', json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  }

  public function sendAction() {
    $webhookurl = "http://phalcon.inf1nitev0id.ru/send/webhook";
    try {
      $data = json_decode($this->request->getPost('data'), true, 512, JSON_THROW_ON_ERROR);
      $json_data = json_encode([
        'hash' => $this->request->getPost('hash'),
        "name" => $this->request->getPost('name'),
        "family" => $this->request->getPost('family'),
        "data" => $data,
        "update" => strToTime($this->request->getPost('update_date')." ".$this->request->getPost('update_time'))
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      echo $json_data;

      $ch = curl_init( $webhookurl );
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_exec($ch);
      curl_close($ch);
      $response = new Phalcon\Http\Response();
      $response->redirect('/users');
      $response->send();
    } catch (JsonException $exc) {
      echo $exc->getTraceAsString();
    }
  }

  public function webhookAction() {
    $this->view->disable();
    $json = file_get_contents('php://input');
    $webhook = json_decode($json, true);
    if ($webhook) {
      $this->write($webhook);
      $response = new Phalcon\Http\Response();
      $response->setStatusCode(200, 'OK');
      $response->send();
    } else {
      http_response_code(400);
      echo "Not OK";
      $response = new Phalcon\Http\Response();
      $response->setStatusCode(400, 'Bad Request');
      $response->send();
    }
  }
}
