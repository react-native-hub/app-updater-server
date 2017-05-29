<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

$app->get('/api/v1', function() use ($app) {

    return $app->json(array(
                'version' => '1.0.0',
                'status' => 'ok',
    ));
});

$app->get('/api/v1/newbuild', function (Request $request) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Versions WHERE version>> ? ORDER BY version ASC LIMIT 1', array($request->query->get('current_version')));

    if (!empty($result)) {
        return $app->json(
                        array(
                            'update' => true,
                            'url' => $app['updater.url'] . '/v1/version/' . $result['id'] . '/download',
                            'version' => $result['version'],
                            'checksum' => $result['checksum'],
                        )
        );
    }


    return $app->json(
                    array(
                        'update' => false,
                    )
    );
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


    $file = $request->files->get('file');

    if ($file === null) {
        return $app->json(array('error' => '501', 'message' => 'you need to upload a file'), 501);
    }

    if (file_exists($app['updater.file_path']) === false) {
        mkdir($app['updater.file_path'], 644, true);
    }
    
    $file->move($app['updater.file_path'], $file->getClientOriginalName());

    $checkSumFile = hash_file('sha256', $app['updater.file_path'] . $file->getClientOriginalName());

    if ($checkSumFile !== $request->request->get('checksum')) {
        return $app->json(array('error' => '501', 'message' => 'The checksum of the file is not the same as specified'), 501);
    }


    $resultSql = $app['db']->insert('Versions', array(
        'idApp' => $request->request->get('id_app'),
        'idPlatform' => $request->request->get('id_platform'),
        'version' => $request->request->get('version'),
        'checksum' => $checkSumFile, //sha256
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

$app->get('/api/v1/versions/{id}/download', function( $id) use ($app) {

    $result = $app['db']->fetchAssoc('SELECT * FROM Versions WHERE id=?', array($id));
    if ($result === false) {
        $result = array(
            'error' => 404,
            'message' => 'Not found',
        );
        $code = 404;
    }

    return $app->sendFile(__DIR__ . '/../files/' . $result['checksum'])
                    ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename('main.js.bundle'));
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
