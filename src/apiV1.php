<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/api/v1', function() use ($app) {

    return $app->json(array(
                'version' => '1.0.0',
                'status' => 'ok',
    ));
});

$app->get('/api/v1/newbuild', function (Request $request) use ($app) {

    if ($request->query->get('version') == '1.0.0') {
        return $app->json(
                        array(
                            'update' => true,
                            'url' => 'http://url.nl/api/v1/download/hash',
                            'version' => '1.0.1'
                        )
        );
    }


    return $app->json(
                    array(
                        'update' => false,
                    )
    );
});

$app->post('/api/v1/build', function(Request $request) use ($app) {

    $request->request->get('id_project');
    $request->request->get('version');



    return $app->json(array('build'));
});

/**
 * Versions
 */
$app->get('/api/v1/versions', function() use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Versions');
    if ($result === false) {
        $result = array();
    }
    return $app->json($result, 200);
});

$app->post('/api/v1/versions', function(Request $request) use ($app) {

    $resultSql = $app['db']->insert('Versions', array(
        'idApp' => $request->request->get('id_app'), 
        'idPlatform' => $request->request->get('id_platform'),
        'version' => $request->request->get('version'),
        'checksum' => $request->request->get('checksum'),
        'creationTime' => date('Y-m-d H:i:s')
            )
            );

    if ($resultSql === false) {
        $result = array(
            'error' => '501',
            'message' => 'Version not saved',
        );
        $code = 501;
    } else {

        $result = $app['db']->fetchAssoc('SELECT * FROM Verions WHERE id=?', array($app['db']->lastInsertId()));
    }
    return $app->json($result, (isset($code)) ? $code : 201);
});

$app->get('/api/v1/versions/{id}', function($id) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Versions WHERE id=?', array($id));
    if ($result === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});

$app->delete('/api/v1/versions/{id}', function( $id) use ($app) {

    $resultSql = $app['db']->delete('Versions', array('id' => $id));
    $result = array();
    if ($resultSql === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 204);
});


/**
 * App
 */
$app->get('/api/v1/apps', function() use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Apps');
    if ($result === false) {
        $result = array();
    }
    return $app->json($result, 200);
});

$app->post('/api/v1/apps', function(Request $request) use ($app) {

    $resultSql = $app['db']->insert('Apps', array('name' => $request->request->get('name'), 'baseUrl' => $request->request->get('base_url')));

    if ($resultSql === false) {
        $result = array(
            'error' => '501',
            'message' => 'App not saved',
        );
        $code = 501;
    } else {

        $result = $app['db']->fetchAssoc('SELECT * FROM Apps WHERE id=?', array($app['db']->lastInsertId()));
    }
    return $app->json($result, (isset($code)) ? $code : 201);
});

$app->get('/api/v1/apps/{id}', function($id) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Apps WHERE id=?', array($id));
    if ($result === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});

$app->put('/api/v1/apps/{id}', function(Request $request, $id) use ($app) {

    $resultSql = $app['db']->update('Apps', array('name' => $request->request->get('name'), 'baseUrl' => $request->request->get('base_url')), array('id' => $id));

    if ($resultSql === false) {
        $result = array(
            'error' => '501',
            'message' => 'App not updated',
        );
        $code = 501;
    } else {

        $result = $app['db']->fetchAssoc('SELECT * FROM Apps WHERE id=?', array($id));
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});

$app->delete('/api/v1/app/{id}', function( $id) use ($app) {

    $resultSql = $app['db']->delete('Apps', array('id' => $id));
    $result = array();
    if ($resultSql === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 204);
});



$app->post('/api/v1/apps/{id}/addplatform', function($id) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Apps WHERE id=?', array($id));
    if ($result === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    } else {
        
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});

$app->delete('/api/v1/apps/{id}/deleteplatform', function($id) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Apps WHERE id=?', array($id));
    if ($result === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});


/**
 * Platform
 */
$app->get('/api/v1/platforms', function() use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Platforms');
    if ($result === false) {
        $result = array();
    }
    return $app->json($result, 200);
});

$app->post('/api/v1/platforms', function(Request $request) use ($app) {

    $resultSql = $app['db']->insert('Platforms', array('name' => $request->request->get('name')));

    if ($resultSql === false) {
        $result = array(
            'error' => '501',
            'message' => 'Platform not saved',
        );
        $code = 501;
    } else {

        $result = $app['db']->fetchAssoc('SELECT * FROM Platforms WHERE id=?', array($app['db']->lastInsertId()));
    }
    return $app->json($result, (isset($code)) ? $code : 201);
});

$app->get('/api/v1/platforms/{id}', function($id) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Platforms WHERE id=?', array($id));
    if ($result === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});

$app->put('/api/v1/platforms/{id}', function(Request $request, $id) use ($app) {

    $resultSql = $app['db']->update('Platforms', array('name' => $request->request->get('name')), array('id' => $id));

    if ($resultSql === false) {
        $result = array(
            'error' => '501',
            'message' => 'Platform not updated',
        );
        $code = 501;
    } else {

        $result = $app['db']->fetchAssoc('SELECT * FROM Platforms WHERE id=?', array($id));
    }
    return $app->json($result, (isset($code)) ? $code : 200);
});

$app->delete('/api/v1/platforms/{id}', function( $id) use ($app) {

    $resultSql = $app['db']->delete('Platforms', array('id' => $id));
    $result = array();
    if ($resultSql === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }
    return $app->json($result, (isset($code)) ? $code : 204);
});
