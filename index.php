<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <title>Youtube Tool - Comment Crawler</title>
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href=".">Youtube Tool</a>
                </div>
                <ul class="nav navbar-nav">
                    <li class="active"><a href=".">Comment Crawler</a></li>
                </ul>
            </div>
        </nav>

        <?php
        
        require_once 'code.php';

        $text_data = '';
        $file_exported = '';
        $keywords = '';
        $keymain = '';
        $maxresult = 5;
        $minview = 0;
        $mincomment = 0;
        $minlike = 0;
        $publish_at = '01-01-2001';
        $error_text = '';
        $check_input = true;
        $is_post = false;
        
        $input_keyword = '';
        $input_keymain = '';
        
        if (isset($_POST['action']) && ($_POST['action'] == 'crawler')) {
            
            $is_post = true;
            
            $input_keyword = $_POST['search_keywords'];

            if (!empty($input_keyword) || $input_keyword != '') {
                $keywords = explode("\n", str_replace("\r", "", $input_keyword));
                $keywords = array_map('trim', $keywords);
                foreach ($keywords as $key => $value) {
                    if (empty($value)) {
                        unset($keywords[$key]);
                    }
                }
            }
            $input_keymain = $_POST['keyword'];
            $keymain = $input_keymain;
            
            if (isset($_POST['maxresult']) && $_POST['maxresult']) {
                $maxresult = $_POST['maxresult'];
            }
            if (isset($_POST['minview']) && $_POST['minview']) {
                $minview = $_POST['minview'];
            }
            if (isset($_POST['mincomment']) && $_POST['mincomment']) {
                $mincomment = $_POST['mincomment'];
            }
            if (isset($_POST['minlike']) && $_POST['minlike']) {
                $minlike = $_POST['minlike'];
            }
            if (isset($_POST['published_at']) && $_POST['published_at']) {
                $publish_at = $_POST['published_at'];
            }
            
            $file_exported = 'youtube_exported.csv';
            
            if (!$keywords) {
                $check_input = false;
                $error_text .= "Search keywords are empty!";
            }
            if (!$keymain) {
                $check_input = false;
                if ($error_text)
                    $error_text .= '<br/>';
                $error_text .= "Keyword are empty!";
            }
        }
        ?>

        <div id="page-wrapper">
            <?php if ($is_post) { ?>
            <div class="row">
                <div class="col-lg-12" style="margin-top: 20px;">
                    <div class="panel panel-default">
                        <div class="panel-heading">Thông tin đang xử lý...
                        </div>
                        <div class="panel-body">
                                    <div class="col-md-12"> 
                                        <div class="form-group">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-hover">
                                                <tbody>
                                                    <?php
                                                    if ($check_input) {
                                                        $final_result = array();
                                                        $video_ids = array();
                                                        
                                                        foreach ($keywords as $keyword) {
                                                            echo "<tr>
                                                                        <td>Keyword đang xử lý: <b>$keyword</b></td>
                                                                  </tr>";
                                                            flush();
                                                            
                                                            $temp_result['keyword'] = $keyword;
                                                            $temp_get_result = get_result($video_ids, $keyword, $keymain, $maxresult, $minview, $minlike, $mincomment, $publish_at);
                                                            
//                                                            echo "<pre>";
//                                                            print_r($temp_get_result);
//                                                            echo "<pre>";
                                                            
                                                            $temp_result['result'] = $temp_get_result['result'];
                                                            $video_ids = array_merge((array)$video_ids, (array)$temp_get_result['video_ids']);
                                                            
//                                                            echo "<pre>";
//                                                            print_r($video_ids);
//                                                            echo "<pre>";
                                                            
                                                            $final_result[] = $temp_result;
                                                        }
                                                        
//                                                        echo "<pre>";
//                                                        print_r($final_result);
//                                                        echo "<pre>";
//                                                        exit;
                                                        
                                                        $fp = fopen($file_exported, 'w');
                                                        
                                                        fputcsv($fp, array("Keyword","Comment Link","Video without links"));
                                                        
                                                        $result_comments = array();
                                                        foreach ($final_result as $keyword_result) {
                                                            $output = array();
                                                            $output[0][0] = $keyword_result['keyword'];
                                                            $count = -1;
//                                                            fputcsv($fp, array("Keyword: " . $keyword_result['keyword']));
                                                            if (count($keyword_result['result']) > 0) {
                                                                foreach ($keyword_result['result'] as $video_id => $comment_list) {
                                                                    if (!empty($comment_list)) {
                                                                        foreach ($comment_list as $comment_id) {
                                                                            $count++;
                                                                            $comment_link = "https://www.youtube.com/watch?v=$video_id&lc=$comment_id";
                                                                            $result_comments[] = $comment_link;
                                                                            if (!isset($output[$count][0])) {
                                                                                $output[$count][0] = ' ';
                                                                            }
                                                                            $output[$count][1] = $comment_link;
                                                                            if (!isset($output[$count][2])) {
                                                                                $output[$count][2] = ' ';
                                                                            }
                                                                        }
                                                                    }
                                                                }
    //                                                            fputcsv($fp, array("Matched Filter nhung khong co comment chua tu khoa"));
                                                                $count_empty = -1;
                                                                foreach ($keyword_result['result'] as $video_id => $comment_list) {
                                                                    if (empty($comment_list)) {
                                                                        $count_empty++;
                                                                        $video_link = "https://www.youtube.com/watch?v=$video_id";
                                                                        if (!isset($output[$count_empty][0])) {
                                                                                $output[$count_empty][0] = ' ';
                                                                        }
                                                                        if (!isset($output[$count_empty][1])) {
                                                                            $output[$count_empty][1] = ' ';
                                                                        }
                                                                        $output[$count_empty][2] = $video_link;
                                                                    }
                                                                }
                                                            }
                                                            
//                                                            echo "<pre>";
//                                                            print_r($output);
//                                                            echo "<pre>";
                                                            
                                                            foreach ($output as $key => $value) {
                                                                fputcsv($fp, $value);
                                                            }
                                                            
                                                        }
                                                        
//                                                        exit;
                                                        
                                                        fclose($fp);
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        </div>
                                        </div>
                        </div>
                                        </div>
                                    </div>
                                    <?php } ?>
            <div class="row">
                <div class="col-lg-12" style="margin-top: 20px;">
                    <div class="panel panel-default">
                        <div class="panel-heading">Crawler Youtube Comment by keywords, filters...
                        </div>
                        <div class="panel-body">
<?php if ($error_text) { ?>
                                <div class="alert alert-danger"><?php echo $error_text ?></div>
                            <?php } ?>
                            <div class="row show-grid">
                                <form method="POST">
                                    <div class="col-md-8" style="margin-bottom: 20px">
                                        <input type="hidden" name="action" value="crawler"/>
                                        <button type="submit" class="btn btn-success" value="submit">Submit</button>
                                        <button type="reset" class="btn btn-default">Reset</button>

<?php if ($file_exported) { ?>
                                            <label style="margin-left: 10px;">Result:</label>
                                            <a href='youtube_exported.csv'><b>Download Here</b></a>
<?php } ?>
                                    </div>
                                    <div class="col-md-4">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Keyword</label>
                                            <input class="form-control" id="keyword" name="keyword" value="<?php echo $input_keymain ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Enter search keywords (keyword per line):</label>
                                            <textarea data-autoresize class="form-control" rows="10" id="search_keywords" name="search_keywords" style="resize:vertical;"><?php echo $input_keyword ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-group">
                                                <label>Max Results</label>
                                                <input class="form-control" name="maxresult" type="number" min="1" value="<?php echo $maxresult ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Min Views</label>
                                                <input class="form-control" name="minview" type="number" min="0" value="<?php echo $minview ?>">
                                                <p class="help-block">Enter 0 to remove filter.</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Min Comments</label>
                                                <input class="form-control" name="mincomment" type="number" min="0" value="<?php echo $mincomment ?>">
                                                <p class="help-block">Enter 0 to remove filter.</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Min Likes</label>
                                                <input class="form-control" name="minlike" type="number" min="0" value="<?php echo $minlike ?>">
                                                <p class="help-block">Enter 0 to remove filter.</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Publish At Before</label>
                                                <div class="input-append date" id="datepicker" data-date="<?php echo $publish_at ?>" data-date-format="dd-mm-yyyy">
                                                    <input class="span2 form-control" name="published_at" size="16" type="text" value="<?php echo $publish_at ?>">
                                                    <span class="add-on"><i class="icon-th"></i></span>
                                                </div>
                                                <p class="help-block">Keep default value to remove filter.</p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (isset($_POST['action']) && !empty($_POST['action'])) { 
                
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
                
                ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Checking Links
                        </div>
                        <div class="panel-body">
                            <div class="row show-grid">
                                <form method="POST">
                                    <div class="col-md-8" style="margin-bottom: 20px">
                                        <input type="hidden" name="action" value="find_links"/>
                                        <button type="submit" class="btn btn-success" value="submit">Submit</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </div>
                                    <div class="col-md-4">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Link trong format trả kết quả</label>
                                            <input class="form-control" id="input_format_tra_ket_qua" name="input_format_tra_ket_qua" value="<?php echo @$input_format_tra_ket_qua ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Link cần tìm:</label>
                                            <textarea data-autoresize class="form-control" rows="10" id="input_link_can_tim" name="input_link_can_tim" style="resize:vertical;"><?php if (isset($input_link_can_tim) && !empty($input_link_can_tim)) echo implode("\r\n", $input_link_can_tim) ?></textarea>
                                        </div>
                                        <div class="form-group hidden">
                                            <label>List crawler:</label>
                                            <textarea data-autoresize class="form-control" rows="10" id="input_list_crawler" name="input_list_crawler" style="resize:vertical;"><?php if (isset($result_comments) && !empty($result_comments)) echo implode("\r\n", $result_comments) ?></textarea>
                                        </div>
                                        <?php if (isset($_POST['action']) && $_POST['action'] == 'find_links') { ?>
                                            <div class="form-group">
                                                <label>Checking Links Result:</label>
                                                <span style="display: block; color: green; font-weight: bold;">
                                                    <?php 
                                                        if (isset($result_pos) && !empty($result_pos)) {
                                                            echo "No comment on link " . implode(", ", $result_pos) . "<br/>";
                                                        }
                                                        echo "Open the page again to get " . count(@$result_pos) . " new videos <br/>";
                                                        echo @$_POST['input_format_tra_ket_qua'];
                                                    ?>
                                                </span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php } ?>
            
            <footer class="page-footer font-small teal pt-4">
                <div class="footer-copyright py-3" style='text-align: right'>© 2018 Developer by
                    <a href='skype:live:tuandao.dev?chat'> Tuan Dao</a>
                </div>
            </footer>
        </div>

        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="css/custom.css">
        <link rel="stylesheet" href="css/datepicker.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <script src="js/bootstrap-datepicker.js"></script>
        <script>
            jQuery.each(jQuery('textarea[data-autoresize]'), function () {
                var offset = this.offsetHeight - this.clientHeight;

                var resizeTextarea = function (el) {
                    jQuery(el).css('height', 'auto').css('height', el.scrollHeight + offset);
                };
                jQuery(this).on('keyup input', function () {
                    resizeTextarea(this);
                }).removeAttr('data-autoresize');
            });

            $(document).ready(function () {
                $('#search_keywords').keyup();

                $('#datepicker').datepicker();
            });
        </script>
    </body>
</html>
