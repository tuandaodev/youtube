<?php

include_once 'config.php';
require_once('google-api/vendor/autoload.php');

function get_commentThreads($service, $video_id, $keymain) {
//    $url = "https://www.googleapis.com/youtube/v3/commentThreads?key=" . DEVELOPER_KEY . "&textFormat=plainText&part=snippet%2Creplies&videoId=$video_id&maxResults=50";
    
    $continue = true;
    $result = array();
    $pageToken = '';
    
    if (is_array($keymain)) {
        $keymain_text = implode(',', $keymain);
    } else {
        $keymain_text = $keymain;
    }
    
    while ($continue) {
        if ($pageToken) {
//            $data_single = get_paged($url, $pageToken);
            $params = array('videoId' => $video_id, 'pageToken' => $pageToken, 'maxResults' => 100, 'searchTerms' => $keymain_text);
            $data_single = commentThreadsListByVideoId($service, 'snippet,replies', $params);
        } else {
//            $data_single = make_call($url);
            $params = array('videoId' => $video_id, 'maxResults' => 100, 'searchTerms' => $keymain_text);
            $data_single = commentThreadsListByVideoId($service, 'snippet,replies', $params);
        }
        
        if (isset($data_single['items'])) {
            
            foreach ($data_single['items'] as $comments) {
                if (isset($comments['snippet']['topLevelComment']['snippet']['textOriginal'])) {
                    $comment_text = $comments['snippet']['topLevelComment']['snippet']['textOriginal'];
                    $comment_text = strtolower($comment_text);
                    
                    if (is_array($keymain)) {
                        foreach ($keymain as $key) {
                            if (strpos($comment_text, $key) !== false) {
                                $result[] = $comments['id'];
                                continue;
                            }
                        }
                        
                    } else {
                        if (strpos($comment_text, $keymain) !== false) {
                            $result[] = $comments['id'];
                        }
                    }
                    
                }
                if (isset($comments['replies']['comments']) && count($comments['replies']['comments'] > 0)) {
                    foreach ($comments['replies']['comments'] as $reply) {
                        $reply_text = $reply['snippet']['textOriginal'];
                        $reply_text = strtolower($reply_text);
                        if (is_array($keymain)) {
                            foreach ($keymain as $key) {
                                if (strpos($reply_text, $key) !== false) {
                                    $result[] = $reply['id'];
                                }
                            }
                        } else {
                            if (strpos($reply_text, $keymain) !== false) {
                                $result[] = $reply['id'];
                            }
                        }
                    }
                }
            }
        } else {
            $continue = false;
        }
        
        if (isset($data_single['nextPageToken'])) {
            $pageToken = $data_single['nextPageToken'];
        } else {
            $pageToken = '';
            $continue = false;
        }
    }
    
    return $result;
}

function commentThreadsListByVideoId($service, $part, $params) {
    $params = array_filter($params);
    $response = $service->commentThreads->listCommentThreads(
        $part,
        $params
    );

    return $response;
}

function videosListMultipleIds($service, $part, $params) {
    $params = array_filter($params);
    $response = $service->videos->listVideos(
        $part,
        $params
    );
    
    return $response;
}