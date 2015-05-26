<?php

use Feedr\Router\Route\AccessPoint;

$router->get('atom-feed', '/atom/{id}/{title}/feed.xml', function(AccessPoint $route) use ($xmlTemplate, $dbConnection, $request) {
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);
    $timeAgo      = new \Feedr\Format\TimeAgo();

    header('Content-Type: application/atom+xml');

    return $xmlTemplate->render('feed.pxml', [
        'id'  => $request->getBaseUrl() . '/atom/' . $route->getVariable('id') . '/' . $route->getVariable('title') . '/feed.xml',
        'feed' => $feedDatabase->getFeed($route->getVariable('id'), $timeAgo),
    ]);
});

$router->get('login', '/login', function() use ($htmlTemplate, $github, $request, $user) {
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
    $router->get('home', '/', function() use ($request) {
        header('Location: ' . $request->getBaseUrl() . '/login');
        exit;
    });

    return;
}

$router->post('logout', '/logout', function() use ($csrfToken, $request, $user) {
    if ($csrfToken->validate($request->post('csrf-token'))) {
        $user->logout();
    }

    header('Location: ' . $request->getBaseUrl());
    exit;
});

$router->get('home', '/', function() use ($htmlTemplate, $csrfToken, $user, $dbConnection) {
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);
    $logDatabase  = new \Feedr\Storage\Database\Log($dbConnection);

    $timeAgo      = new \Feedr\Format\TimeAgo();
    $url          = new \Feedr\Presentation\Url();

    $feeds = $feedDatabase->getFeeds($user->get('id'));

    $posts = [];

    if (count($feeds)) {
        reset($feeds);
        $firstKey = key($feeds);

        $posts = $feedDatabase->getPosts($firstKey, $timeAgo);
    }

    return $htmlTemplate->render('home.phtml', [
        'csrfToken' => $csrfToken,
        'feeds'     => $feeds,
        'logs'      => $logDatabase->getLogItemLimited($user->get('id'), $timeAgo),
        'posts'     => $posts,
        'url'       => $url,
    ]);
});

$router->get('create', '/create', function() use ($htmlTemplate, $csrfToken, $user) {
    return $htmlTemplate->render('create.phtml', [
        'csrfToken' => $csrfToken,
        'user'      => $user,
    ]);
});

$router->post('create', '/create', function() use ($request, $dbConnection, $user) {
    $authDatabase = new \Feedr\Storage\Database\Auth($dbConnection);
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);

    $feedDatabase->create($request, $user, $authDatabase);

    header('Location: ' . $request->getBaseUrl());
    exit;
});

$router->get('search-repository', '/search-repository', function() use ($request, $github) {
    if (filter_var($request->get('repo'), FILTER_VALIDATE_URL) !== false || substr_count($request->get('repo'), '/') === 1) {
        $parts = explode('/', $request->get('repo'));

        $repo  = array_pop($parts);
        $owner = array_pop($parts);

        return '[' . $github->request('/repos/' . $owner . '/' . $repo) . ']';
    }

    $results = json_decode($github->request('/search/repositories?q=' . urlencode($request->get('repo'))), true);

    return json_encode($results['items']);
});

$router->get('get-releases', '/get-releases', function() use ($github, $request) {
    $releases = [];
    $repos    = $request->get('repos');
    $ids      = array_column($repos, 'fullname');

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

$router->get('search-user', '/search-user', function() use ($request, $github) {
    return $github->request('/search/users?q=' . urlencode($request->get('user')));
});

$router->get('edit-feed', '/feeds/{id}/{name}', function(AccessPoint $route) use ($request, $htmlTemplate, $csrfToken, $user, $dbConnection) {
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);

    if (!$feedDatabase->isAdmin($user->get('id'), $route->getVariable('id'))) {
        header('Location: ' . $request->getBaseUrl());
        exit;
    }

    $timeAgo      = new \Feedr\Format\TimeAgo();

    $url          = new \Feedr\Presentation\Url();

    $feed         = $feedDatabase->getFeed($route->getVariable('id'), $timeAgo, false);

    $jsonRepos = [];
    foreach ($feed['repos'] as $repo) {
        $jsonRepos[] = [
            'id'       => $repo['repository_id'],
            'fullname' => $repo['repository'],
        ];
    }

    return $htmlTemplate->render('edit.phtml', [
        'csrfToken' => $csrfToken,
        'user'      => $user,
        'feed'      => $feed,
        'jsonRepos' => json_encode($jsonRepos),
        'url'       => $url,
    ]);
});

$router->post('edit-feed', '/feeds/{id}/{name}', function(AccessPoint $route) use ($csrfToken, $user, $dbConnection, $request) {
    $authDatabase = new \Feedr\Storage\Database\Auth($dbConnection);
    $feedDatabase = new \Feedr\Storage\Database\Feed($dbConnection);

    if (!$feedDatabase->isAdmin($user->get('id'), $route->getVariable('id'))) {
        header('Location: ' . $request->getBaseUrl());
        exit;
    }

    $feedDatabase->update($route->getVariable('id'), $request, $user, $authDatabase);

    header('Location: ' . $request->getBaseUrl());
});
