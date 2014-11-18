<?php

use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth2\Token\StdOAuth2Token;

require_once __DIR__ . '/../bootstrap.php';

$credentials = new Credentials(
    $githubCredentials['key'],
    $githubCredentials['secret'],
    'http://github.com'
);
$github = $serviceFactory->createService('GitHub', $credentials, $storage, array());

$token = new StdOAuth2Token();
$token->setAccessToken($githubCredentials['cli_token']);
$token->setEndOfLife(StdOAuth2Token::EOL_NEVER_EXPIRES);

$storage->storeAccessToken('GitHub', $token);

$stmt = $dbConnection->query('SELECT id, feed_id, repository FROM feeds_repositories ORDER BY id ASC');

$repositories = [];

$feeds = $stmt->fetchAll();

foreach ($feeds as $repository) {
    $repositories[] = $repository['repository'];
}

$repositories = array_unique($repositories);

$result = [];

foreach ($repositories as $repository) {
    $result[$repository] = json_decode($github->request('repos/' . $repository . '/releases'), true);
}

foreach ($feeds as $feed) {
    $timestamp = new \DateTime();

    foreach ($result[$feed['repository']] as $release) {
        if (postExists($dbConnection, $feed['feed_id'], $release['id'])) {
            continue;
        }

        $postId = addPost($dbConnection, $feed['id'], $release);

        logAddition($dbConnection, $feed['feed_id'], $postId, $timestamp);
    }
}

function postExists(\PDO $dbConnection, $feedId, $releaseId) {
    $query = 'SELECT count(posts.id)';
    $query.= ' FROM posts';
    $query.= ' JOIN feeds_repositories ON feeds_repositories.id = posts.feed_repository_id';
    $query.= ' WHERE feeds_repositories.feed_id = :feedid';
    $query.= ' AND release_id = :releaseid';

    $stmt = $dbConnection->prepare($query);
    $stmt->execute([
        'feedid'    => $feedId,
        'releaseid' => $releaseId,
    ]);

    return !!$stmt->fetchColumn(0);
}

function addPost(\PDO $dbConnection, $feedRepositoryId, array $release) {
    $query = 'INSERT INTO posts';
    $query.= ' (release_id, feed_repository_id, avatar_url, version, url, timestamp, content)';
    $query.= ' VALUES';
    $query.= ' (:releaseid, :feed_repository_id, :avatar_url, :version, :url, :timestamp, :content)';

    $stmt = $dbConnection->prepare($query);
    $stmt->execute([
        'releaseid'          => $release['id'],
        'feed_repository_id' => $feedRepositoryId,
        'avatar_url'         => $release['author']['avatar_url'],
        'version'            => $release['tag_name'],
        'url'                => $release['html_url'],
        'timestamp'          => (new \DateTime($release['published_at']))->format('Y-m-d H:i:s'),
        'content'            => $release['body'],
    ]);

    return $dbConnection->lastInsertId('posts_id_seq');
}

function logAddition(\PDO $dbConnection, $feedId, $postId, \DateTime $timestamp) {
    $query = 'INSERT INTO log';
    $query.= ' (feed_id, post_id, timestamp, type)';
    $query.= ' VALUES';
    $query.= ' (:feedid, :postid, :timestamp, :type)';

    $stmt = $dbConnection->prepare($query);
    $stmt->execute([
        'feedid'    => $feedId,
        'postid'    => $postId,
        'timestamp' => $timestamp->format('Y-m-d H:i:s'),
        'type'      => 'newPost',
    ]);
}
