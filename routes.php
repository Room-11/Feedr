<?php

use Feedr\Router\Route\AccessPoint;

$router->get('atom-feed', '/atom/{id}/{title}/feed.xml', function(AccessPoint $route) use ($xmlTemplate) {
    header('Content-Type: application/atom+xml');

    return $xmlTemplate->render('feed.pxml', [
        'var' => 'value',
    ]);
});

$router->get('login', '/login', function(AccessPoint $route) use ($htmlTemplate, $github, $request, $user) {
    if ($request->get('code')) {
        $github->requestAccessToken($request->get('code'));

        $result = json_decode($github->request('user'), true);

        if ($user->login($result, $request->server('REMOTE_ADDR'))) {
            header('Location: ' . $request->getBaseUrl());
            exit;
        }
    }

    return $htmlTemplate->render('login.phtml', [
        'url' => $github->getAuthorizationUri(),
    ]);
});

if (!$user->isLoggedIn()) {
    $router->get('home', '/', function(AccessPoint $route) use ($request) {
        header('Location: ' . $request->getBaseUrl() . '/login');
        exit;
    });

    return;
}

$router->post('logout', '/logout', function(AccessPoint $route) use ($csrfToken, $request, $user) {
    if ($csrfToken->validate($request->post('csrf-token'))) {
        $user->logout();
    }

    header('Location: ' . $request->getBaseUrl());
    exit;
});

$router->get('home', '/', function(AccessPoint $route) use ($htmlTemplate, $csrfToken, $user, $dbConnection) {
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);
    $logDatabase  = new \Feedr\Storage\Database\Log($dbConnection);

    $timeAgo      = new \Feedr\Format\TimeAgo();

    $feeds = $feedDatabase->getFeeds($user->get('id'));

    $posts = [];

    if (count($feeds)) {
        $firstFeed = reset($feeds);
        $firstKey  = key($feeds);

        $posts = $feedDatabase->getPosts($firstKey, $timeAgo);
    }

    return $htmlTemplate->render('home.phtml', [
        'csrfToken' => $csrfToken,
        'feeds'     => $feeds,
        'logs'      => $logDatabase->getLogItemLimited($user->get('id'), $timeAgo),
        'posts'     => $posts,
    ]);
});

$router->get('create', '/create', function(AccessPoint $route) use ($htmlTemplate, $csrfToken, $user) {
    return $htmlTemplate->render('create.phtml', [
        'csrfToken' => $csrfToken,
        'user'      => $user,
    ]);
});

$router->post('create', '/create', function(AccessPoint $route) use ($request, $dbConnection, $user) {
    $authDatabase = new \Feedr\Storage\Database\Auth($dbConnection);
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);

    $feedDatabase->create($request, $user, $authDatabase);

    header('Location: ' . $request->getBaseUrl());
    exit;
});

$router->get('search-repository', '/search-repository', function(AccessPoint $route) use ($request, $github) {
    if (filter_var($request->get('repo'), FILTER_VALIDATE_URL) !== false) {
        $parts = explode('/', $request->get('repo'));
    } elseif (substr_count($request->get('repo'), '/') === 1) {
        //$repositoryParts = explode('/', $request->get('repo'));
    } else {
    }

    $repo  = array_pop($parts);
    $owner = array_pop($parts);

    return '[' . $github->request('/repos/' . $owner . '/' . $repo) . ']';
});

$router->get('get-releases', '/get-releases', function(AccessPoint $route) use ($github, $request) {
    $releases = [];
    $ids      = $request->get('ids');

    $timeAgo  = new \Feedr\Format\TimeAgo();

    foreach ($ids as $id) {
        $repoReleases = json_decode($github->request('repos/' . $id . '/releases'), true);

        foreach ($repoReleases as $release) {
            $body = $release['body'];
            if (strlen($body) > 100) {
                $body = substr($body, 0, 100) . '...';
            }

            $releases[$release['published_at']] = [
                'url'         => $release['html_url'],
                'version'     => $release['tag_name'],
                'name'        => $id,
                'avatar'      => $release['author']['avatar_url'],
                'description' => $body,
                'timestamp'   => $timeAgo->calculate(new \DateTime($release['published_at'])),
            ];
        }
    }

    ksort($releases);

    return json_encode($releases);
});

$router->get('search-user', '/search-user', function(AccessPoint $route) use ($request, $github) {
    return $github->request('/search/users?q=' . urlencode($request->get('user')));
});
