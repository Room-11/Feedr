<?php

use Feedr\Router\Route\AccessPoint;

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

$router->get('atom-feed', '/atom/{id}/{title}/feed.xml', function(AccessPoint $route) {
    $xml = '<?xml version="1.0" encoding="utf-8"?>';
    $xml.= '<feed xmlns="http://www.w3.org/2005/Atom">';
    $xml.= '    <id>https://feedr.pieterhordijk.com</id>';
    $xml.= '    <title>Feedr test feed \0/</title>';
    $xml.= '    <updated>2014-11-17T00:30:02Z</updated>';
    $xml.= '    <author>';
    $xml.= '        <name>PeeHaa</name>';
    $xml.= '        <uri>http://stackoverflow.com/users/508666/peehaa</uri>';
    $xml.= '    </author>';
    $xml.= '    <link href="https://feedr.pieterhordijk.com/atom/1/demo-feed/feed.xml"/>';
    $xml.= '    <generator uri="https://github.com/Room-11/Feedr" version="1.0">';
    $xml.= '        Feedr';
    $xml.= '    </generator>';
    $xml.= '    <entry>';
    $xml.= '        <id>http://example.com/blog/1234</id>';
    $xml.= '        <title>Test entry</title>';
    $xml.= '        <updated>2014-11-17T00:30:02-05:00</updated>';
    $xml.= '        <author>';
    $xml.= '            <name>PeeHaa</name>';
    $xml.= '            <content>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vitae tortor nisi. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris eu ante vel nunc posuere egestas. In vehicula rhoncus aliquam. Duis sit amet fringilla nisl. Quisque hendrerit diam mi, a facilisis neque malesuada volutpat. Phasellus risus tellus, luctus eget fermentum a, finibus a magna. Mauris dignissim id nunc aliquam gravida. Vestibulum quis quam at felis rhoncus varius. Proin lectus nulla, auctor sit amet placerat egestas, ultricies sit amet mauris. Morbi ut nisi sed leo vestibulum varius. Nullam aliquam, quam sit amet ultricies sollicitudin, mauris ipsum molestie purus, eget lobortis ipsum mi cursus est. Fusce vitae consequat est. Phasellus tempus est ut scelerisque placerat. Curabitur posuere lacus et sapien porttitor molestie. Aenean pellentesque volutpat lectus ut vulputate. Quisque eget maximus libero.</content>';
    $xml.= '            <link rel="alternate" href="https://github.com/PeeHaa/OpCacheGUI/releases/tag/v1.0.0-rc1"/>';
    $xml.= '            <summary>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vitae tortor nisi. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</summary>';
    $xml.= '        </author>';
    $xml.= '    </entry>';
    $xml.= '</feed>';

    //header('Content-Type: application/atom+xml');
    header('Content-Type: text/xml');
    return $xml;
});
