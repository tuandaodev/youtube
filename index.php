<?php

include_once 'config.php';
require_once('google-api/vendor/autoload.php');
require_once('functions.php');

$keywords = 'dota2 highlight, dota2 navi';
$keymain = 'notail,dendi';

$client = new Google_Client();
$client->setDeveloperKey(DEVELOPER_KEY);
$youtube = new Google_Service_YouTube($client);


$keywords = explode(',', $keywords);
$keymain = explode(',', $keymain);

$final_result = array();
foreach ($keywords as $keyword) {
    $temp_result['keyword'] = $keyword;
    $temp_result['result'] = get_result($youtube, $keyword, $keymain, 5, 0, 0, 0, 0);
    
    $final_result[] = $temp_result;
}

echo "<pre>";
print_r($final_result);
echo "<pre>";
exit;

function get_result($youtube, $keyword, $keymain, $maxResults = 50, $minview = 0, $minlike = 0, $mincomment = 0, $published = 0) {
    try {
        $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $keyword,
            'maxResults' => $maxResults,
        ));

        $list_video = array();

        foreach ($searchResponse['items'] as $searchResult) {
            if ($searchResult['id']['kind'] == 'youtube#video') {

                $publishedAt = strtotime($searchResult['snippet']['publishedAt']);
                $list_video[$searchResult['id']['videoId']] = array(
                    'id' => $searchResult['id']['videoId'],
                    'title' => $searchResult['snippet']['title'],
                    'publishedAt' => date('d-m-Y H:i:s', $publishedAt),
                );
                $list_id[] = $searchResult['id']['videoId'];
            }
        }

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
        
//        echo "<pre>";
//        print_r($list_video);
//        echo "<pre>";
//        exit;

        $result = array();
        foreach ($list_id as $video_id) {
            $temp = get_commentThreads($youtube, $video_id, $keymain);
            if (!empty($temp)) {
                $result[$video_id] = $temp;
            }
        }
        
        return $result;
        
    } catch (Google_Service_Exception $e) {
        echo sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
        echo sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    }
}
?>