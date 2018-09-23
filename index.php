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
        
        $input_keyword = '';
        $input_keymain = '';
        
        if (isset($_POST) && !empty($_POST)) {
            $input_keyword = $_POST['search_keywords'];

            if (!empty($input_keyword) || $input_keyword != '') {
                $keywords = explode("\n", str_replace("\r", "", $input_keyword));
                $keywords = array_map('trim', $keywords);
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
            
//            echo "<pre>";
//            print_r($_POST);
//            echo "<pre>";
//            exit;
            
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

            if ($check_input) {
                $final_result = array();
                foreach ($keywords as $keyword) {
                    $temp_result['keyword'] = $keyword;
                    $temp_result['result'] = get_result($keyword, $keymain, $maxresult, $minview, $minlike, $mincomment, $publish_at);

                    $final_result[] = $temp_result;
                    
                }
                
                $file_exported = 'youtube_exported.csv';
                $fp = fopen($file_exported, 'w');
                foreach ($final_result as $keyword_result) {
                    fputcsv($fp, array($keyword_result['keyword']));
                    foreach ($keyword_result['result'] as $video_id => $comment_list) {
                        foreach ($comment_list as $comment_id) {
                            $comment_link = "https://www.youtube.com/watch?v=$video_id&lc=$comment_id";
                            $temp = array($comment_link);
                            fputcsv($fp, $temp);
                        }
                    }
                }
                fclose($fp);
            }
        }
        ?>

        <div id="page-wrapper">
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
                                            <input class="form-control" id="keyword" name="keyword" value="<?php $input_keymain ?>">
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
