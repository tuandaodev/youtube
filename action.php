<?php

$return = array();
$return['status'] = "0";

if (isset($_POST['action']) && !empty($_POST['action'])) {
                
    if (isset($_POST['action']) && $_POST['action'] == 'find_links') {

        $input_format_tra_ket_qua = @$_POST['input_format_tra_ket_qua'];

        $input_list_crawler = explode("\n", str_replace("\r", "", $_POST['input_list_crawler']));
        $input_list_crawler = array_map('trim', $input_list_crawler);
        foreach ($input_list_crawler as $key => $value) {
            if (empty($value)) {
                unset($input_list_crawler[$key]);
            }
        }
        $result_comments = $input_list_crawler;

        $input_link_can_tim = explode("\n", str_replace("\r", "", $_POST['input_link_can_tim']));
        $input_link_can_tim = array_map('trim', $input_link_can_tim);
        foreach ($input_link_can_tim as $key => $value) {
            if (empty($value)) {
                unset($input_link_can_tim[$key]);
            }
        }

        $count_pos = 0;
        $result_pos = array();
        foreach ($input_link_can_tim as $link) {
            $count_pos++;
            if (!in_array($link, $input_list_crawler)) {
                $result_pos[] = $count_pos;
            }
        }
    }
    
    $result = "<label>Checking Links Result:</label>
                <span style='display: block; color: green; font-weight: bold;'>";
                        if (isset($result_pos) && !empty($result_pos)) {
                            $result .= "No comment on link " . implode(", ", $result_pos) . "<br/>";
                        }
                        $result .= "Open the page again to get " . count(@$result_pos) . " new videos <br/>";
                        $result .= @$_POST['input_format_tra_ket_qua'];
                $result .= "</span>";
    
    $return['status'] = "1";
    $return['html'] = $result;
}

echo json_encode($return);