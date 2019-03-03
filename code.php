<?php

include_once 'config.php';
require_once('google-api/vendor/autoload.php');
require_once('functions.php');

function get_result($video_ids, $keyword, $keymain, $maxResults = 50, $minview = 0, $minlike = 0, $mincomment = 0, $published_at = '01-01-2001') {
    
    $client = new Google_Client();
    $client->setDeveloperKey(DEVELOPER_KEY);
    $youtube = new Google_Service_YouTube($client);
    
    $return = array();
    $list_id = array();
    
    try {
        $list_video = array();
        $continue = true;
        $pageToken = '';
        $count_item = 0;
        
        $check_date_main = false;
        if ($published_at != '01-01-2001') {
            $published_date = strtotime($published_at);
        } else {
            $check_date_main = true;
        }
        
        while ($continue) {
            if ($pageToken) {
                $params = array( 'q' => $keyword, 'maxResults' => 50, 'pageToken' => $pageToken);
            } else {
                $params = array( 'q' => $keyword, 'maxResults' => 50);
            }
            
//            echo "<pre>";
//            print_r($params);
//            echo "</pre>";
//            exit;
            
            $searchResponse = $youtube->search->listSearch('id,snippet', $params);
            
//            echo "<pre>";
//            print_r($searchResponse);
//            echo "</pre>";
//            exit;
            
            foreach ($searchResponse['items'] as $searchResult) {
                if ($searchResult['id']['kind'] == 'youtube#video') {
                    $count_item++;
                    $check_date = false;
                    $publishedAt = strtotime($searchResult['snippet']['publishedAt']);
                    
                    if (!$check_date_main) {
                        if ($published_date > $publishedAt) {
                            $check_date = true;
                        } else {
                            $check_date = false;
                        }
                    } else {
                        $check_date = true;
                    }
                    
                    $check_id = true;
                    if (!empty($video_ids) && in_array($searchResult['id']['videoId'], $video_ids)) {
                        $check_id = false;
                    }
                    
                    if ($check_date && $check_id) {
                        $list_video[$searchResult['id']['videoId']] = array(
                            'id' => $searchResult['id']['videoId'],
                            'title' => $searchResult['snippet']['title'],
                            'publishedAt' => date('d-m-Y H:i:s', $publishedAt),
                            'time'  => $publishedAt,
                        );
                        $list_id[] = $searchResult['id']['videoId'];
                    }
                    if ($count_item == $maxResults) {
                        $continue = false;
                        break;
                    }
                }
            }
            if (!isset($searchResponse['items'])) {
                $continue = false;
            }
            if (isset($searchResponse['nextPageToken']) && !empty($searchResponse['nextPageToken'])) {
                $pageToken = $searchResponse['nextPageToken'];
            } else {
                $continue = false;
                $pageToken = '';
            }
            
        }
        
        $return['video_ids'] = $list_id;
        $list_id_string = implode(',', $list_id);

        $videos_data_temp = videosListMultipleIds($youtube, 'statistics', array('id' => $list_id_string));
        
        if (isset($videos_data_temp['items']) && count($videos_data_temp['items']) > 0) {
            foreach ($videos_data_temp['items'] as $video) {
                $statis_temp['comment'] = $video['statistics']['commentCount'];
                $statis_temp['like'] = $video['statistics']['likeCount'];
                $statis_temp['view'] = $video['statistics']['viewCount'];

                $list_video[$video['id']] = array_merge($list_video[$video['id']], $statis_temp);
            }
        }
        
        foreach ($list_video as $key => $video) {
            if ($minlike > 0 && $video['like'] < $minlike) {
                unset($list_video[$key]);
            } elseif ($minview > 0 && $video['view'] < $minview) {
                unset($list_video[$key]);
            } elseif ($mincomment > 0 && $video['comment'] < $mincomment) {
                unset($list_video[$key]);
            }
        }
        
        usort($list_video, 'sortByView');
        
//        echo "<pre>";
//        print_r($list_video);
//        echo "</pre>";
//        exit;
        
        $result = array();
        foreach ($list_id as $video_id) {
            $temp = get_commentThreads($youtube, $video_id, $keymain);
            $result[$video_id] = $temp;
            if (!empty($temp)) {
                foreach ($temp as $comment_id) {
                    $comment_link = "https://www.youtube.com/watch?v=$video_id&lc=$comment_id";
                    echo "<tr>
                        <td>$comment_link</td>
                    </tr>";
                    flush();
                }
            }
        }
        
//        echo "<tr>
//                <td><b>Video Matched filters nhưng không có comment chứa nội dung</b></td>
//          </tr>";
//        
//        foreach ($result as $video_id => $data) {
//            if (empty($data)) {
//                $video_link = "https://www.youtube.com/watch?v=$video_id";
//                echo "<tr>
//                    <td>$video_link</td>
//                </tr>";
//            }
//        }
//        flush();
        
        $return['result'] = $result;
        return $return;
        
    } catch (Google_Service_Exception $e) {
//        return array();
//        echo sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
//        return array();
//        echo sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    }
}

function sortByView($a, $b) {
    return $b['view'] - $a['view'];
}
?>