<?php
try
{
  $loader = new Phalcon\Loader();
  $loader->registerDirs(array(
    '../app/controllers/',
    '../app/models/'
  ))->register();

  $di = new Phalcon\DI\FactoryDefault();

  $di->set('mongo', function() {
    $mongo = new Phalcon\Db\Adapter\Mongo\Client("mongodb://localhost:27017");
    return $mongo->selectDB('test_webhooks');
  }, true);

  // $di->setShared('collectionManager', function () {
  //   return new Phalcon\Mvc\Collection\Manager();
  // });

  $di->set('view', function() {
    $view = new Phalcon\Mvc\View();
    $view->setViewsDir('../app/views/');
    return $view;
  });

  $di->set('url', function() {
    $url = new Phalcon\Mvc\Url();
    $url->setBaseUri('/');
    return $url;
  });

  $di->set('router', function() {
    $router = new Phalcon\Mvc\Router();
    $router->add('/users/([0-9]+)', [
        'controller' => 'users',
        'action'     => 'index',
        'page'       => 1
    ]);
    $router->add('/users/edit/([0-9]+)', [
        'controller' => 'users',
        'action'     => 'edit',
        'hash'       => 1
    ]);
    $router->add('/users/delete/([0-9]+)', [
        'controller' => 'users',
        'action'     => 'delete',
        'hash'       => 1
    ]);
    return $router;
  });

  $application = new \Phalcon\Mvc\Application();
  $application->setDI($di);
  $request = new Phalcon\Http\Request();
  echo $application->handle($request->getURI())->getContent();

}
catch(\Phalcon\Exception $e)
{
  echo "PhalconException: ", $e->getMessage();
}
