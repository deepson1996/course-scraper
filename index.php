<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrape coursera courses with category</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

</head>

<body>
    <main>
        <nav class="navbar navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Coursera Scraper</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>

        <section class="container">
            <div class="card mt-5">
                <div class="card-body">
                    <h5 class="card-title mb-3">Select a category to scrape course details.</h5>
                    <form action="" method="post" name="category_form" name="category_form">
                        <div class="mb-3">
                            <label for="category" class="form-label">Select Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="data-science">Data Science</option>
                                <option value="business">Business</option>
                                <option value="computer-science">Computer Science</option>
                                <option value="personal-development">Personal Development</option>
                                <option value="information-technology">Information Technology</option>
                                <option value="language-learning">Language Learning</option>
                                <option value="health">Health</option>
                                <option value="math-and-logic">Math and Logic</option>
                                <option value="social-sciences">Social Sciences</option>
                                <option value="physical-science-and-engineering">Physical Science and Engineering</option>
                                <option value="arts-and-humanities">Arts and Humanities</option>
                            </select>
                        </div>
                        <input type="submit" value="submit" name="submit" class="btn btn-primary"></input>

                    </form>
                </div>
            </div>

            <?php
            ini_set('max_execution_time', '300');
            include "simple_html_dom.php";
            ?>
            <?php
            if ((isset($_POST['submit']) && $_POST['category'])) {
                $html = file_get_html("https://www.coursera.org/browse/" . $_POST['category']);
                $courseDetail = getCourseDetail($html);
                echo ("<br><pre>");
                // print_r($courseDetail);
                echo "</pre>";
                writeToCSV($courseDetail, $_POST['category']);
            }


            function getCourseDetail($html)
            {
                $courses = array();
                $i = 0;
                foreach ($html->find('a.CardText-link') as  $course) {
                    // $courses[$i]['link'] = $course->href;
                    // if ($i < 3) {
                    $courseHtml = file_get_html("https://www.coursera.org" . $course->href);

                    $courses[$i]['category_name'] = $courseHtml->find('div._1ruggxy a', 1)->plaintext;

                    $courses[$i]['course_name'] = $course->plaintext;

                    $courses[$i]['first_instructor'] = $courseHtml->find('h3.instructor-name', 0)->plaintext;

                    $providers = "";
                    foreach ($courseHtml->find('h3.rc-Partner__title') as $key => $provider) {
                        if ($key != 0) {
                            $providers .= ", ";
                        }
                        $providers .= $provider->plaintext;
                    }
                    $courses[$i]['course_provider'] = $providers;

                    $description = "";
                    foreach ($courseHtml->find('div.description p') as $descs) {
                        $description .= $descs->plaintext;
                    }
                    $courses[$i]['course_description'] = $description;

                    $courses[$i]['no_of_students_enrolled']  = $courseHtml->find('div.Banner div.horizontal-box div.rc-ProductMetrics span strong span', 0)->plaintext;

                    $courses[$i]['no_of_ratings']  = str_replace("ratings", "", $courseHtml->find('div.Banner div.XDPRating span[data-test=ratings-count-without-asterisks] span', 0)->plaintext);
                    // }
                    $i++;
                }
                return $courses;
            }

            function writeToCSV($courseDetail, $category)
            {
                $f = fopen("csv_files/" . time() . "_" . $category . ".csv", 'w');
                fputcsv($f, array('Course Category', 'Course Name', 'First Instructor', 'Course Provider', 'Course Description', "No Of Students Enrolled", "No Of Ratings"));
                foreach ($courseDetail as $key => $value) {
                    fputcsv($f, $value);
                }
                fclose($f);
            }
            ?>

            <?php
            $path    = 'csv_files';
            $files = scandir($path);
            $files = array_diff(scandir($path), array('.', '..'));
            rsort($files);
            ?>
            <?php if (count($files) > 0) { ?>
                <div class="card mt-5">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Recently Scraped Courses List.</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($files as $key => $file) { ?>
                                <li class="list-group-item"><a href="csv_files/<?php echo $file ?>"><?php echo $file ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>

        </section>
    </main>




</body>

</html>